<?php

declare(strict_types=1);

namespace PhpDb\Adapter\Sqlite\Platform;

use PhpDb\Adapter\Platform\PlatformInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

/**
 * @psalm-suppress UnusedClass
 */
final class PlatformFactory implements FactoryInterface
{
    public function __invoke(
        ContainerInterface $container,
        string $requestedName,
        array|null $options = null
    ): PlatformInterface {
        return new Sqlite();
    }
}
