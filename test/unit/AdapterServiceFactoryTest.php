<?php

namespace LaminasTest\Db\Sqlite;

use Laminas\Db\Adapter\Profiler\Profiler;
use Laminas\Db\Adapter\Profiler\ProfilerInterface;
use Laminas\Db\Sqlite\Adapter;
use Laminas\Db\Sqlite\AdapterServiceFactory;
use Laminas\Db\Sqlite\ConfigProvider;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\ServiceManager\ServiceManager;
use Override;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

use function extension_loaded;

#[CoversMethod(AdapterServiceFactory::class, '__invoke')]
final class AdapterServiceFactoryTest extends TestCase
{
    private AdapterServiceFactory $factory;

    protected function createServiceManager(array $dbConfig): ServiceLocatorInterface
    {
        $config                       = (new ConfigProvider())->getDependencies();
        $config['services']['config'] = $dbConfig;

        return new ServiceManager($config);
    }

    #[Override]
    protected function setUp(): void
    {
        if (! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('Adapter factory tests require pdo_sqlite');
        }

        $this->factory = new AdapterServiceFactory();
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testV3FactoryReturnsDefaultAdapter(): void
    {
        $this->expectNotToPerformAssertions();

        $services = $this->createServiceManager([
            'db' => [
                'driver'   => 'Pdo_Sqlite',
                'database' => ':memory:',
            ],
        ]);

        $this->factory->__invoke($services, Adapter::class);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testV3FactoryReturnsDefaultAdapterWithDefaultProfiler(): void
    {
        $services = $this->createServiceManager([
            'db' => [
                'driver'   => 'Pdo_Sqlite',
                'database' => ':memory:',
                'profiler' => true,
            ],
        ]);

        $adapter = $this->factory->__invoke($services, Adapter::class);
        self::assertInstanceOf(ProfilerInterface::class, $adapter->getProfiler());
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testV3FactoryReturnsDefaultAdapterWithProfilerClassname(): void
    {
        $services = $this->createServiceManager([
            'db' => [
                'driver'   => 'Pdo_Sqlite',
                'database' => ':memory:',
                'profiler' => Profiler::class,
            ],
        ]);

        $adapter = $this->factory->__invoke($services, Adapter::class);
        self::assertInstanceOf(ProfilerInterface::class, $adapter->getProfiler());
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testV3FactoryReturnsDefaultAdapterWithProfilerInstance(): void
    {
        $services = $this->createServiceManager([
            'db' => [
                'driver'   => 'Pdo_Sqlite',
                'database' => ':memory:',
                'profiler' => $this->getMockBuilder(ProfilerInterface::class)->getMock(),
            ],
        ]);

        $adapter = $this->factory->__invoke($services, Adapter::class);
        self::assertInstanceOf(ProfilerInterface::class, $adapter->getProfiler());
    }
}
