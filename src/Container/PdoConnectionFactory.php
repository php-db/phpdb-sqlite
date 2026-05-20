<?php

declare(strict_types=1);

namespace PhpDb\Sqlite\Container;

use PhpDb\Adapter\Driver\PdoConnectionInterface;
use PhpDb\Adapter\Exception\InvalidConnectionParametersException;
use PhpDb\Sqlite\Pdo\Connection;
use Psr\Container\ContainerInterface;

use function is_array;

final class PdoConnectionFactory
{
    public function __invoke(
        ContainerInterface $container,
        string $requestedName,
        ?array $options = null
    ): PdoConnectionInterface&Connection {
        $conn = $options['connection'] ?? [];
        if (! is_array($conn) || $conn === []) {
            throw new InvalidConnectionParametersException(
                'Connection configuration must be an array of parameters passed via $options["connection"]',
                $conn
            );
        }

        return new Connection($conn);
    }
}
