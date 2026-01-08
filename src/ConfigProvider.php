<?php

declare(strict_types=1);

namespace PhpDb\Adapter\Sqlite;

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
use PhpDb\Container\AbstractAdapterInterfaceFactory;
use PhpDb\Metadata\MetadataInterface;
use PhpDb\ResultSet;

final class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies(),
        ];
    }

    public function getDependencies(): array
    {
        return [
            'abstract_factories' => [
                AbstractAdapterInterfaceFactory::class,
            ],
            'aliases'            => [
                'SQLite'                            => Pdo\Driver::class,
                'Sqlite'                            => Pdo\Driver::class,
                'sqlite'                            => Pdo\Driver::class,
                'pdo'                               => Pdo\Driver::class,
                'pdo_sqlite'                        => Pdo\Driver::class,
                'pdosqlite'                         => Pdo\Driver::class,
                'pdodriver'                         => Pdo\Driver::class,
                ConnectionInterface::class          => Pdo\Connection::class,
                PdoConnectionInterface::class       => Pdo\Connection::class,
                DriverInterface::class              => Pdo\Driver::class,
                PdoDriverInterface::class           => Pdo\Driver::class,
                PlatformInterface::class            => AdapterPlatform::class,
                ProfilerInterface::class            => Profiler::class,
                ResultInterface::class              => Result::class,
                ResultSet\ResultSetInterface::class => ResultSet\ResultSet::class,
                StatementInterface::class           => Statement::class,
                MetadataInterface::class            => Metadata\Source::class,
            ],
            'factories'          => [
                AdapterInterface::class    => Container\AdapterInterfaceFactory::class,
                Pdo\Connection::class      => Container\PdoConnectionFactory::class,
                Pdo\Driver::class          => Container\PdoDriverFactory::class,
                Result::class              => Container\PdoResultFactory::class,
                Statement::class           => Container\PdoStatementFactory::class,
                AdapterPlatform::class     => Container\PlatformInterfaceFactory::class,
                Profiler::class            => InvokableFactory::class,
                ResultSet\ResultSet::class => InvokableFactory::class,
                Metadata\Source::class     => Container\MetadataInterfaceFactory::class,
            ],
        ];
    }
}
