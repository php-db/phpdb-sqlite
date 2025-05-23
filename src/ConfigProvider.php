<?php
declare(strict_types=1);

namespace Laminas\Db\Sqlite;

use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\Adapter\Driver\DriverInterface;
use Laminas\Db\Adapter\Platform\PlatformInterface;
use Laminas\Db\Adapter\Profiler\Profiler;
use Laminas\Db\Adapter\Profiler\ProfilerInterface;
use Laminas\ServiceManager\Factory\InvokableFactory;

readonly class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies(),
        ];
    }

    public function getDependencies(): array
    {
        return [
            'aliases'   => [
                PlatformInterface::class => Platform\Sqlite::class,
                ProfilerInterface::class => Profiler::class,
            ],
            'factories' => [
                AdapterInterface::class => AdapterServiceFactory::class,
                DriverInterface::class  => Driver\Pdo\DriverFactory::class,
                Platform\Sqlite::class  => InvokableFactory::class,
                Profiler::class         => InvokableFactory::class,
            ],
        ];
    }
}