<?php

namespace LaminasTest\Db\Sqlite;

use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\Adapter\Driver\DriverInterface;
use Laminas\Db\Adapter\Platform\PlatformInterface;
use Laminas\Db\Adapter\Profiler\Profiler;
use Laminas\Db\Adapter\Profiler\ProfilerInterface;
use Laminas\Db\Sqlite\AdapterServiceFactory;
use Laminas\Db\Sqlite\Driver;
use Laminas\Db\Sqlite\Module;
use Laminas\Db\Sqlite\Platform;
use Laminas\ServiceManager\Factory\InvokableFactory;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

#[CoversMethod(Module::class, 'getConfig')]
final class ModuleTest extends TestCase
{
    /** @var array<string, array<array-key, string>> */
    private array $config = [
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

    public function testProvidesExpectedConfiguration(): Module
    {
        $provider = new Module();
        self::assertEquals(['service_manager' => $this->config], $provider->getConfig());

        return $provider;
    }
}
