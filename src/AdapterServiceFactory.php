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
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array|null         $options
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @return Adapter
     */
    public function __invoke(ContainerInterface $container, string $requestedName, ?array $options = null): Adapter
    {
        $resultSetPrototype = $container->has(ResultSetInterface::class) ? $container->get(ResultSetInterface::class) : null;
        $profiler           = $this->createProfiler($container, $this->getConfig($container));

        return new Adapter(
            $container->get(DriverInterface::class),
            $container->get(Platform\Sqlite::class),
            $resultSetPrototype,
            $profiler
        );
    }
}