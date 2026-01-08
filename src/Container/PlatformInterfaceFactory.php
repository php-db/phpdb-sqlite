<?php

declare(strict_types=1);

namespace PhpDb\Adapter\Sqlite\Container;

use PDO;
use PhpDb\Adapter\Driver\PdoDriverInterface;
use PhpDb\Adapter\Platform\PlatformInterface;
use PhpDb\Adapter\Sqlite\AdapterPlatform;
use Psr\Container\ContainerInterface;

final class PlatformInterfaceFactory
{
    public function __invoke(ContainerInterface $container): PlatformInterface&AdapterPlatform
    {
        /** @var array $config */
        $config = $container->get('config');

        /** @var array $dbConfig */
        $dbConfig = $config['db'] ?? [];

        /** @var string $driver */
        $driver = $dbConfig['driver'];

        /** @var PdoDriverInterface|PDO $driverInstance */
        $driverInstance = $container->get($driver);

        return new AdapterPlatform($driverInstance);
    }

    public static function fromDriver(PdoDriverInterface $driverInstance): PlatformInterface&AdapterPlatform
    {
        return new AdapterPlatform($driverInstance);
    }
}
