<?php

declare(strict_types=1);

namespace PhpDb\Adapter\Sqlite\Container;

use PhpDb\Adapter\Sqlite\Driver\Pdo\Pdo;
use PhpDb\Container\ConnectionInterfaceFactoryFactoryInterface as FactoryFactoryInterface;
use Psr\Container\ContainerInterface;
use RuntimeException;

use function array_key_exists;
use function sprintf;

final class ConnectionInterfaceFactoryFactory implements FactoryFactoryInterface
{
    public function __invoke(
        ?ContainerInterface $container = null,
        ?string $requestedName = null
    ): callable {
        $adapterConfig = $container->get('config')['db']['adapters'] ?? [];
        if (! isset($adapterConfig[$requestedName]['driver'])) {
            throw new RuntimeException(sprintf(
                'Named adapter "%s" is not configured with a driver',
                $requestedName
            ));
        }
        $adapterServices  = $container->get('config')['dependencies'];
        $configuredDriver = $adapterConfig[$requestedName]['driver'];
        if (array_key_exists($configuredDriver, $adapterServices['aliases'])) {
            $aliasTo = $adapterServices['aliases'][$configuredDriver];
        } else {
            $aliasTo = $configuredDriver;
        }
        return match ($aliasTo) {
            Pdo::class    => new PdoConnectionFactory(),
            default       => throw new RuntimeException(sprintf(
                'No connection factory found for driver "%s"',
                $configuredDriver
            )),
        };
    }
}
