<?php

declare(strict_types=1);

namespace PhpDbIntegrationTest\Sqlite\Container\TestAsset;

use Laminas\ServiceManager\ServiceManager;
use Laminas\Stdlib\ArrayUtils;
use Override;
use PhpDb\Adapter\AdapterInterface;
use PhpDb\Adapter\Driver\DriverInterface;
use PhpDb\ConfigProvider as LaminasDbConfigProvider;
use PhpDb\Sqlite\ConfigProvider;
use PhpDb\Sqlite\Pdo\Driver;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use Psr\Container\ContainerInterface;

/**
 * This trait provides a setup method for integration tests that require
 * a database adapter configuration.
 *
 * It initializes the service manager with a database configuration,
 * allowing for the creation of an adapter manager and the retrieval
 * of an adapter instance.
 */
#[RequiresPhpExtension('pdo_sqlite')]
trait SetupTrait
{
    protected array $config = [AdapterInterface::class => []];

    protected ?AdapterInterface $adapter;

    protected ContainerInterface&ServiceManager $container;

    protected DriverInterface|string|null $driver;

    #[Override]
    protected function setUp(): void
    {
        $this->getAdapter();
        parent::setUp();
    }

    protected function getAdapter(array $config = []): AdapterInterface
    {
        $connectionConfig = [
            AdapterInterface::class => [
                'driver'     => $this->driver ?? Driver::class,
                'connection' => [
                    'dsn'            => 'sqlite::memory:',
                    'charset'        => 'utf8',
                    'driver_options' => [],
                ],
                'options'    => [
                    //'buffer_results' => false,
                ],
            ],
        ];

        // merge service config from both PhpDb and PhpDb\Sqlite
        $serviceManagerConfig = ArrayUtils::merge(
            (new LaminasDbConfigProvider())()['dependencies'],
            (new ConfigProvider())()['dependencies']
        );

        $serviceManagerConfig = ArrayUtils::merge(
            $serviceManagerConfig,
            $connectionConfig
        );

        // prefer passed config over environment variables
        if ($config !== []) {
            $serviceManagerConfig = ArrayUtils::merge($serviceManagerConfig, $config);
        }

        $serviceManagerConfig = ArrayUtils::merge(
            $serviceManagerConfig,
            [
                'services' => [
                    'config' => $serviceManagerConfig,
                ],
            ]
        );

        $this->config    = $serviceManagerConfig;
        $this->container = new ServiceManager($this->config);
        $this->adapter   = $this->container->get(AdapterInterface::class);

        return $this->adapter;
    }

    protected function getConfig(): array
    {
        return $this->config;
    }

    protected function getHostname(): string
    {
        return $this->getConfig()['db']['connection']['hostname'];
    }
}
