<?php

declare(strict_types=1);

namespace PhpDb\Sqlite\Container;

use PhpDb\Adapter\Platform\PlatformInterface;
use PhpDb\Exception\ContainerException;
use PhpDb\Sqlite\AdapterPlatform;
use PhpDb\Sqlite\Pdo\Driver;
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
