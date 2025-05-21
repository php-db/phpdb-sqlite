<?php

namespace Laminas\Db\Sqlite;

use Laminas\Db\Adapter\Driver\DriverInterface;
use Laminas\Db\Adapter\Exception;
use Laminas\Db\Adapter\Profiler;
use Laminas\Db\ResultSet\ResultSetInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class AdapterServiceFactory implements FactoryInterface
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
        $config             = $container->get('config');
        $resultSetPrototype = $container->has(ResultSetInterface::class) ? $container->get(ResultSetInterface::class) : null;
        $profiler           = $this->createProfiler($container, $config['db']['profiler'] ?? []);

        return new Adapter(
            $container->get(DriverInterface::class),
            $container->get(Platform\Sqlite::class),
            $resultSetPrototype,
            $profiler
        );
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function createProfiler(ContainerInterface $container, array $parameters): ?Profiler\ProfilerInterface
    {
        if ($parameters['profiler'] instanceof Profiler\ProfilerInterface) {
            return $parameters['profiler'];
        }

        if (is_string($parameters['profiler']) && $container->has($parameters['profiler'])) {
            return $container->get($parameters['profiler']);
        }

        if (is_bool($parameters['profiler'])) {
            return $parameters['profiler'] === true ? new Profiler\Profiler() : null;
        }

        throw new Exception\InvalidArgumentException(
            '"profiler" parameter must be an instance of ProfilerInterface or a boolean'
        );
    }
}