<?php

declare(strict_types=1);

namespace PhpDb\Adapter\Sqlite\Container;

use PhpDb\Adapter\Driver\ConnectionInterface;
use PhpDb\Adapter\Sqlite\Driver\Pdo\Connection;
use Psr\Container\ContainerInterface;

final class PdoConnectionFactory
{
    public function __invoke(ContainerInterface $container): ConnectionInterface&Connection
    {
        /** @var array $config */
        $config = $container->get('config');

        /** @var array $dbConfig */
        $dbConfig = $config['db'] ?? [];

        /** @var array $connectionConfig */
        $connectionConfig = $dbConfig['connection'] ?? [];

        return new Connection($connectionConfig);
    }
}
