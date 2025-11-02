<?php

declare(strict_types=1);

namespace PhpDbTest\Adapter\Sqlite;

use Laminas\ServiceManager\Factory\InvokableFactory;
use PhpDb\Adapter\AdapterInterface;
use PhpDb\Adapter\Driver\ConnectionInterface;
use PhpDb\Adapter\Driver\DriverInterface;
use PhpDb\Adapter\Driver\Pdo\Result;
use PhpDb\Adapter\Driver\Pdo\Statement;
use PhpDb\Adapter\Driver\PdoConnectionInterface;
use PhpDb\Adapter\Driver\PdoDriverInterface;
use PhpDb\Adapter\Driver\ResultInterface;
use PhpDb\Adapter\Driver\StatementInterface;
use PhpDb\Adapter\Platform\PlatformInterface;
use PhpDb\Adapter\Profiler\Profiler;
use PhpDb\Adapter\Profiler\ProfilerInterface;
use PhpDb\Adapter\Sqlite\ConfigProvider;
use PhpDb\Adapter\Sqlite\Container;
use PhpDb\Adapter\Sqlite\Driver;
use PhpDb\Adapter\Sqlite\Metadata;
use PhpDb\Adapter\Sqlite\Platform;
use PhpDb\Container\AdapterManager;
use PhpDb\Metadata\MetadataInterface;
use PhpDb\ResultSet;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;

#[CoversMethod(ConfigProvider::class, '__invoke')]
#[CoversMethod(ConfigProvider::class, 'getDependencies')]
#[CoversMethod(ConfigProvider::class, 'getAdapterManagerConfig')]
final class ConfigProviderTest extends TestCase
{
    /** @var array<string, array<string, string|array<int,string>>> */
    private array $config = [
        'aliases'    => [
            MetadataInterface::class => Metadata\Source\SqliteMetadata::class,
        ],
        'factories'  => [
            Metadata\Source\SqliteMetadata::class => Container\MetadataInterfaceFactory::class,
        ],
        'delegators' => [
            AdapterManager::class => [
                Container\AdapterManagerDelegator::class,
            ],
        ],
    ];

    private array $adapterManagerConfig = [
        'aliases'   => [
            'SQLite'                            => Driver\Pdo\Pdo::class,
            'Sqlite'                            => Driver\Pdo\Pdo::class,
            'sqlite'                            => Driver\Pdo\Pdo::class,
            'pdo'                               => Driver\Pdo\Pdo::class,
            'pdo_sqlite'                        => Driver\Pdo\Pdo::class,
            'pdosqlite'                         => Driver\Pdo\Pdo::class,
            'pdodriver'                         => Driver\Pdo\Pdo::class,
            ConnectionInterface::class          => Driver\Pdo\Connection::class,
            PdoConnectionInterface::class       => Driver\Pdo\Connection::class,
            DriverInterface::class              => Driver\Pdo\Pdo::class,
            PdoDriverInterface::class           => Driver\Pdo\Pdo::class,
            PlatformInterface::class            => Platform\Sqlite::class,
            ProfilerInterface::class            => Profiler::class,
            ResultInterface::class              => Result::class,
            ResultSet\ResultSetInterface::class => ResultSet\ResultSet::class,
            StatementInterface::class           => Statement::class,
        ],
        'factories' => [
            AdapterInterface::class      => Container\AdapterFactory::class,
            Driver\Pdo\Connection::class => Container\PdoConnectionFactory::class,
            Driver\Pdo\Pdo::class        => Container\PdoDriverFactory::class,
            Result::class                => Container\PdoResultFactory::class,
            Statement::class             => Container\PdoStatementFactory::class,
            Platform\Sqlite::class       => Container\PlatformInterfaceFactory::class,
            //Profiler::class              => InvokableFactory::class,
            ResultSet\ResultSet::class => InvokableFactory::class,
        ],
    ];

    public function testProvidesExpectedDependencies(): ConfigProvider
    {
        $provider = new ConfigProvider();
        self::assertEquals($this->config, $provider->getDependencies());

        return $provider;
    }

    public function testProvidesExpectedAdapterManagerConfiguration(): void
    {
        $provider = new ConfigProvider();
        self::assertEquals(
            $this->adapterManagerConfig,
            $provider->getAdapterManagerConfig()
        );
    }

    #[Depends('testProvidesExpectedDependencies')]
    public function testInvocationProvidesDependencyConfiguration(ConfigProvider $provider): void
    {
        self::assertEquals(
            [
                'dependencies'        => $provider->getDependencies(),
                AdapterManager::class => $provider->getAdapterManagerConfig(),
            ],
            $provider()
        );
    }
}
