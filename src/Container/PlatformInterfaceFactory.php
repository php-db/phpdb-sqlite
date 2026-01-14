<?php

declare(strict_types=1);

namespace PhpDb\Adapter\Sqlite\Container;

use PhpDb\Adapter\Platform\PlatformInterface;
use PhpDb\Adapter\Sqlite\AdapterPlatform;
use PhpDb\Adapter\Sqlite\Pdo\Driver;
use PhpDb\Exception\ContainerException;
use Psr\Container\ContainerInterface;

final class PlatformInterfaceFactory
{
    public function __invoke(
        ContainerInterface $container,
        string $requestedName,
        ?array $options = null
    ): PlatformInterface&AdapterPlatform {
        $driverInstance = $options['driver'] ?? null;
        if (! $driverInstance instanceof Driver) {
            throw ContainerException::forService(
                AdapterPlatform::class,
                self::class,
                'Invalid or missing driver provided recieved: '
            );
        }
        return new AdapterPlatform($driverInstance);
    }
}
