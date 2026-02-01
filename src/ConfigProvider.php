<?php

declare(strict_types=1);

namespace PhpDb\Sqlite;

use PhpDb\Adapter\Driver\Pdo\Result;
use PhpDb\Adapter\Driver\Pdo\Statement;
use PhpDb\Adapter\Driver\PdoConnectionInterface;
use PhpDb\Adapter\Driver\PdoDriverInterface;
use PhpDb\Adapter\Driver\ResultInterface;
use PhpDb\Adapter\Driver\StatementInterface;
use PhpDb\Adapter\Platform\PlatformInterface;
use PhpDb\Metadata\MetadataInterface;

final class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies(),
            //AdapterInterface::class => $this->getConfig(),
        ];
    }

    public function getConfig(): array
    {
        return [
            'driver'     => PdoDriverInterface::class,
            'connection' => [
                'dsn' => 'sqlite::memory:',
            ],
        ];
    }

    public function getDependencies(): array
    {
        return [
            'aliases'   => [
                'SQLite'                      => Pdo\Driver::class,
                'Sqlite'                      => Pdo\Driver::class,
                'sqlite'                      => Pdo\Driver::class,
                'pdo'                         => Pdo\Driver::class,
                'pdo_sqlite'                  => Pdo\Driver::class,
                'pdosqlite'                   => Pdo\Driver::class,
                'pdodriver'                   => Pdo\Driver::class,
                PdoConnectionInterface::class => Pdo\Connection::class,
                PdoDriverInterface::class     => Pdo\Driver::class,
                PlatformInterface::class      => AdapterPlatform::class,
                ResultInterface::class        => Result::class,
                StatementInterface::class     => Statement::class,
                MetadataInterface::class      => Metadata\Source::class,
            ],
            'factories' => [
                Pdo\Connection::class  => Container\PdoConnectionFactory::class,
                Pdo\Driver::class      => Container\PdoDriverInterfaceFactory::class,
                Result::class          => Container\PdoResultFactory::class,
                Statement::class       => Container\PdoStatementFactory::class,
                AdapterPlatform::class => Container\PlatformInterfaceFactory::class,
                Metadata\Source::class => Container\MetadataInterfaceFactory::class,
            ],
        ];
    }
}
