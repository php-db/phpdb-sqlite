<?php

declare(strict_types=1);

namespace PhpDb\Sqlite\Container;

use Laminas\ServiceManager\ServiceManager;
use PhpDb\Adapter\Driver\Pdo\Result;
use PhpDb\Adapter\Driver\Pdo\Statement;
use PhpDb\Adapter\Driver\PdoDriverInterface;
use PhpDb\Adapter\Driver\ResultInterface;
use PhpDb\Sqlite\Pdo;
use Psr\Container\ContainerInterface;

final class PdoDriverInterfaceFactory
{
    public function __invoke(
        ContainerInterface&ServiceManager $container,
        string $requestedName,
        ?array $options = null
    ): PdoDriverInterface&Pdo\Driver {
        /** @var Pdo\Connection $connectionInstance */
        $connectionInstance = $container->build(
            Pdo\Connection::class,
            $options
        );

        /** @var ResultInterface&Result $resultInstance */
        $resultInstance = $container->has(ResultInterface::class)
            ? $container->get(ResultInterface::class)
            : new Result();

        return new Pdo\Driver(
            connection:$connectionInstance,
            statementPrototype: $container->build(Statement::class, $options['options'] ?? []),
            resultPrototype: $resultInstance,
            features: [new Pdo\Feature\SqliteRowCounter()],
        );
    }
}
