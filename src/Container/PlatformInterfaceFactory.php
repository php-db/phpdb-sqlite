<?php

declare(strict_types=1);

namespace PhpDb\Adapter\Sqlite\Container;

use PDO;
use PhpDb\Adapter\Driver\PdoDriverInterface;
use PhpDb\Adapter\Platform\PlatformInterface;
use PhpDb\Adapter\Sqlite\Platform\Sqlite;
use Psr\Container\ContainerInterface;

final class PlatformInterfaceFactory
{
    public function __invoke(ContainerInterface $container): PlatformInterface&Sqlite
    {
        /** @var array $config */
        $config = $container->get('config');

        /** @var array $dbConfig */
        $dbConfig = $config['db'] ?? [];

        /** @var string $driver */
        $driver = $dbConfig['driver'];

        /** @var PdoDriverInterface|PDO $driverInstance */
        $driverInstance = $container->get($driver);

        return new Sqlite($driverInstance);
    }

    public static function fromDriver(PdoDriverInterface $driverInstance): PlatformInterface&Sqlite
    {
        return new Sqlite($driverInstance);
    }
}
