<?php

namespace Laminas\Db\Sqlite;

use Laminas\Db\Adapter\AdapterAbstractServiceFactory;
use Laminas\Db\Adapter\Driver\DriverInterface;
use Laminas\Db\ResultSet\ResultSetInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class AdapterServiceFactory extends AdapterAbstractServiceFactory implements FactoryInterface
{
    /**
     * Create db adapter service
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(
        ContainerInterface $container,
        string $requestedName,
        array|null $options = null
    ): Adapter {
        $resultSetPrototype = $container->has(ResultSetInterface::class)
            ? $container->get(ResultSetInterface::class)
            : null;
        $profiler           = $this->createProfiler($container, $this->getConfig($container));

        return new Adapter(
            $container->get(DriverInterface::class),
            $container->get(Platform\Sqlite::class),
            $resultSetPrototype,
            $profiler
        );
    }
}
