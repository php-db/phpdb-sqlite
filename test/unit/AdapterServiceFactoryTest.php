<?php

namespace PhpDbTest\Adapter\Sqlite;

use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\ServiceManager\ServiceManager;
use Override;
use PhpDb\Adapter\Profiler\Profiler;
use PhpDb\Adapter\Profiler\ProfilerInterface;
use PhpDb\Adapter\Sqlite\Adapter;
use PhpDb\Adapter\Sqlite\AdapterServiceFactory;
use PhpDb\Adapter\Sqlite\ConfigProvider;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

use function array_key_exists;
use function extension_loaded;
use function is_array;

#[CoversMethod(AdapterServiceFactory::class, '__invoke')]
final class AdapterServiceFactoryTest extends TestCase
{
    private AdapterServiceFactory $factory;

    protected function createServiceManager(array $dbConfig): ServiceLocatorInterface
    {
        $config = (new ConfigProvider())->getDependencies();
        if (array_key_exists('services', $config) && is_array($config['services'])) {
            $config['services']['config'] = $dbConfig;
        }

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
