<?php

declare(strict_types=1);

namespace Laminas\Db\Sqlite;

use Laminas\Db\Adapter\AbstractAdapter;
use Laminas\Db\Adapter\Driver\DriverInterface;
use Laminas\Db\Adapter\Platform\PlatformInterface;
use Laminas\Db\Sqlite\Driver\Pdo\Driver as SqliteDriver;
use Laminas\Db\Sqlite\Platform\Sqlite as SqlitePlatform;

/**
 * @property SqliteDriver   $driver
 * @property SqlitePlatform $platform
 */
class Adapter extends AbstractAdapter
{
    /** @var SqliteDriver */
    protected $driver;

    /** @var SqlitePlatform */
    protected $platform;

    protected function createDriver(array $parameters): DriverInterface
    {
        return new SqliteDriver($parameters);
    }

    protected function createPlatform(array $parameters): PlatformInterface
    {
        return new SqlitePlatform($this->driver);
    }
}
