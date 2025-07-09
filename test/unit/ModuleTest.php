<?php

namespace PhpDbTest\Adapter\Sqlite;

use PhpDb\Adapter\AdapterInterface;
use PhpDb\Adapter\Driver\DriverInterface;
use PhpDb\Adapter\Platform\PlatformInterface;
use PhpDb\Adapter\Profiler\Profiler;
use PhpDb\Adapter\Profiler\ProfilerInterface;
use PhpDb\Adapter\Sqlite\AdapterServiceFactory;
use PhpDb\Adapter\Sqlite\Driver;
use PhpDb\Adapter\Sqlite\Module;
use PhpDb\Adapter\Sqlite\Platform;
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
