<?php

declare(strict_types=1);

namespace PhpDbTest\Adapter\Sqlite;

use Laminas\ServiceManager\Factory\InvokableFactory;
use PhpDb\Adapter\AdapterInterface;
use PhpDb\Adapter\Driver\ConnectionInterface;
use PhpDb\Adapter\Driver\DriverInterface;
use PhpDb\Adapter\Driver\Pdo\Result;
use PhpDb\Adapter\Driver\Pdo\Statement;
use PhpDb\Adapter\Driver\PdoConnectionInterface;
use PhpDb\Adapter\Driver\PdoDriverInterface;
use PhpDb\Adapter\Driver\ResultInterface;
use PhpDb\Adapter\Driver\StatementInterface;
use PhpDb\Adapter\Platform\PlatformInterface;
use PhpDb\Adapter\Profiler\Profiler;
use PhpDb\Adapter\Profiler\ProfilerInterface;
use PhpDb\Adapter\Sqlite\ConfigProvider;
use PhpDb\Adapter\Sqlite\Container;
use PhpDb\Adapter\Sqlite\Driver\Pdo\Connection;
use PhpDb\Adapter\Sqlite\Driver\Pdo\Pdo;
use PhpDb\Adapter\Sqlite\Metadata\Source\SqliteMetadata;
use PhpDb\Adapter\Sqlite\Platform\Sqlite;
use PhpDb\Container\AdapterAbstractServiceFactory;
use PhpDb\Container\AdapterManager;
use PhpDb\Container\ConnectionInterfaceFactoryFactoryInterface;
use PhpDb\Container\DriverInterfaceFactoryFactoryInterface;
use PhpDb\Container\PlatformInterfaceFactoryFactoryInterface;
use PhpDb\Metadata\MetadataInterface;
use PhpDb\ResultSet;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ConfigProvider::class)]
final class ConfigProviderTest extends TestCase
{
    private ConfigProvider $configProvider;

    protected function setUp(): void
    {
        $this->configProvider = new ConfigProvider();
    }

    public function testInvokeReturnsExpectedStructure(): void
    {
        $config = ($this->configProvider)();

        self::assertIsArray($config);
        self::assertArrayHasKey('dependencies', $config);
        self::assertArrayHasKey(AdapterManager::class, $config);
    }

    public function testGetDependenciesReturnsCorrectStructure(): void
    {
        $dependencies = $this->configProvider->getDependencies();

        self::assertIsArray($dependencies);
        self::assertArrayHasKey('abstract_factories', $dependencies);
        self::assertArrayHasKey('aliases', $dependencies);
        self::assertArrayHasKey('factories', $dependencies);
        self::assertArrayHasKey('delegators', $dependencies);
    }

    public function testGetDependenciesContainsAbstractFactories(): void
    {
        $dependencies = $this->configProvider->getDependencies();

        self::assertContains(
            AdapterAbstractServiceFactory::class,
            $dependencies['abstract_factories']
        );
    }

    public function testGetDependenciesContainsMetadataAlias(): void
    {
        $dependencies = $this->configProvider->getDependencies();

        self::assertArrayHasKey(MetadataInterface::class, $dependencies['aliases']);
        self::assertSame(
            SqliteMetadata::class,
            $dependencies['aliases'][MetadataInterface::class]
        );
    }

    public function testGetDependenciesContainsMetadataFactory(): void
    {
        $dependencies = $this->configProvider->getDependencies();

        self::assertArrayHasKey(SqliteMetadata::class, $dependencies['factories']);
        self::assertSame(
            Container\MetadataInterfaceFactory::class,
            $dependencies['factories'][SqliteMetadata::class]
        );
    }

    public function testGetDependenciesContainsDelegators(): void
    {
        $dependencies = $this->configProvider->getDependencies();

        self::assertArrayHasKey(AdapterManager::class, $dependencies['delegators']);
        self::assertContains(
            Container\AdapterManagerDelegator::class,
            $dependencies['delegators'][AdapterManager::class]
        );
    }

    public function testGetAdapterManagerConfigReturnsCorrectStructure(): void
    {
        $config = $this->configProvider->getAdapterManagerConfig();

        self::assertIsArray($config);
        self::assertArrayHasKey('aliases', $config);
        self::assertArrayHasKey('factories', $config);
        self::assertArrayHasKey('invokables', $config);
    }

    public function testGetAdapterManagerConfigContainsDriverAliases(): void
    {
        $config = $this->configProvider->getAdapterManagerConfig();

        $expectedAliases = [
            'SQLite'     => Pdo::class,
            'Sqlite'     => Pdo::class,
            'sqlite'     => Pdo::class,
            'pdo'        => Pdo::class,
            'pdo_sqlite' => Pdo::class,
            'pdosqlite'  => Pdo::class,
            'pdodriver'  => Pdo::class,
        ];

        foreach ($expectedAliases as $alias => $target) {
            self::assertArrayHasKey($alias, $config['aliases']);
            self::assertSame($target, $config['aliases'][$alias]);
        }
    }

    public function testGetAdapterManagerConfigContainsInterfaceAliases(): void
    {
        $config = $this->configProvider->getAdapterManagerConfig();

        $expectedAliases = [
            ConnectionInterface::class          => Connection::class,
            PdoConnectionInterface::class       => Connection::class,
            DriverInterface::class              => Pdo::class,
            PdoDriverInterface::class           => Pdo::class,
            PlatformInterface::class            => Sqlite::class,
            ProfilerInterface::class            => Profiler::class,
            ResultInterface::class              => Result::class,
            ResultSet\ResultSetInterface::class => ResultSet\ResultSet::class,
            StatementInterface::class           => Statement::class,
        ];

        foreach ($expectedAliases as $interface => $implementation) {
            self::assertArrayHasKey($interface, $config['aliases']);
            self::assertSame($implementation, $config['aliases'][$interface]);
        }
    }

    public function testGetAdapterManagerConfigContainsFactoryFactoryAliases(): void
    {
        $config = $this->configProvider->getAdapterManagerConfig();

        $expectedAliases = [
            ConnectionInterfaceFactoryFactoryInterface::class => Container\ConnectionInterfaceFactoryFactory::class,
            DriverInterfaceFactoryFactoryInterface::class     => Container\DriverInterfaceFactoryFactory::class,
            PlatformInterfaceFactoryFactoryInterface::class   => Container\PlatformInterfaceFactoryFactory::class,
        ];

        foreach ($expectedAliases as $interface => $implementation) {
            self::assertArrayHasKey($interface, $config['aliases']);
            self::assertSame($implementation, $config['aliases'][$interface]);
        }
    }

    public function testGetAdapterManagerConfigContainsFactories(): void
    {
        $config = $this->configProvider->getAdapterManagerConfig();

        $expectedFactories = [
            AdapterInterface::class    => Container\AdapterFactory::class,
            Connection::class          => Container\PdoConnectionFactory::class,
            Pdo::class                 => Container\PdoDriverFactory::class,
            Result::class              => Container\PdoResultFactory::class,
            Statement::class           => Container\PdoStatementFactory::class,
            Sqlite::class              => Container\PlatformInterfaceFactory::class,
            Profiler::class            => InvokableFactory::class,
            ResultSet\ResultSet::class => InvokableFactory::class,
        ];

        foreach ($expectedFactories as $service => $factory) {
            self::assertArrayHasKey($service, $config['factories']);
            self::assertSame($factory, $config['factories'][$service]);
        }
    }

    public function testGetAdapterManagerConfigContainsInvokables(): void
    {
        $config = $this->configProvider->getAdapterManagerConfig();

        $expectedInvokables = [
            Container\ConnectionInterfaceFactoryFactory::class,
            Container\DriverInterfaceFactoryFactory::class,
            Container\PlatformInterfaceFactoryFactory::class,
        ];

        foreach ($expectedInvokables as $invokable) {
            self::assertArrayHasKey($invokable, $config['invokables']);
            self::assertSame($invokable, $config['invokables'][$invokable]);
        }
    }
}
