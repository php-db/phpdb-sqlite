<?php

declare(strict_types=1);

namespace PhpDb\Adapter\Sqlite;

use PhpDb\Adapter\AdapterInterface;
use PhpDb\Adapter\Driver\DriverInterface;
use PhpDb\Adapter\Platform\PlatformInterface;
use PhpDb\Adapter\Profiler\Profiler;
use PhpDb\Adapter\Profiler\ProfilerInterface;
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
