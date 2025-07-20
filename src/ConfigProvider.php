<?php

declare(strict_types=1);

namespace PhpDb\Adapter\Sqlite;

use Laminas\ServiceManager\Factory\InvokableFactory;
use PhpDb\Adapter\AdapterInterface;
use PhpDb\Adapter\Driver\ConnectionInterface;
use PhpDb\Adapter\Driver\DriverInterface;
use PhpDb\Adapter\Driver\PdoDriverInterface;
use PhpDb\Adapter\Driver\ResultInterface;
use PhpDb\Adapter\Driver\StatementInterface;
use PhpDb\Adapter\Driver\Pdo\Result;
use PhpDb\Adapter\Driver\Pdo\Statement;
use PhpDb\Adapter\Platform\PlatformInterface;
use PhpDb\Adapter\Profiler\Profiler;
use PhpDb\Adapter\Profiler\ProfilerInterface;
use PhpDb\Container\AdapterManager;
use PhpDb\Metadata\MetadataInterface;
use PhpDb\ResultSet;

final class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies'        => $this->getDependencies(),
            AdapterManager::class => $this->getAdapterManagerConfig(),
        ];
    }

    public function getDependencies(): array
    {
        return [
            'aliases'   => [
                MetadataInterface::class => Metadata\Source\SqliteMetadata::class,
            ],
            'factories' => [
                Metadata\Source\SqliteMetadata::class => Container\MetadataInterfaceFactory::class,
            ],
            'delegators' => [
                AdapterManager::class => [
                    Container\AdapterManagerDelegator::class,
                ],
            ],
        ];
    }

    public function getAdapterManagerConfig(): array
    {
        return [
            'aliases'   => [
                'SQLite'                            => Driver\Pdo\Pdo::class,
                'Sqlite'                            => Driver\Pdo\Pdo::class,
                'sqlite'                            => Driver\Pdo\Pdo::class,
                'pdo'                               => Driver\Pdo\Pdo::class,
                'pdo_sqlite'                        => Driver\Pdo\Pdo::class,
                'pdosqlite'                         => Driver\Pdo\Pdo::class,
                'pdodriver'                         => Driver\Pdo\Pdo::class,
                ConnectionInterface::class          => Driver\Pdo\Connection::class,
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
                ResultSet\ResultSet::class   => InvokableFactory::class,
            ],
        ];
    }
}
