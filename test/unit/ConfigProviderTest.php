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
use PhpDb\Adapter\Sqlite\AdapterPlatform;
use PhpDb\Adapter\Sqlite\ConfigProvider;
use PhpDb\Adapter\Sqlite\Container;
use PhpDb\Adapter\Sqlite\Metadata;
use PhpDb\Adapter\Sqlite\Pdo\Connection;
use PhpDb\Adapter\Sqlite\Pdo\Driver;
use PhpDb\Container\AbstractAdapterInterfaceFactory;
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

        self::assertArrayHasKey('dependencies', $config);
    }

    public function testGetDependenciesReturnsCorrectStructure(): void
    {
        $dependencies = $this->configProvider->getDependencies();

        self::assertIsArray($dependencies);
        self::assertArrayHasKey('abstract_factories', $dependencies);
        self::assertArrayHasKey('aliases', $dependencies);
        self::assertArrayHasKey('factories', $dependencies);
    }

    public function testGetDependenciesContainsAbstractFactories(): void
    {
        $dependencies = $this->configProvider->getDependencies();

        self::assertContains(
            AbstractAdapterInterfaceFactory::class,
            $dependencies['abstract_factories']
        );
    }

    public function testGetDependenciesContainsMetadataAlias(): void
    {
        $dependencies = $this->configProvider->getDependencies();

        self::assertArrayHasKey(MetadataInterface::class, $dependencies['aliases']);
        self::assertSame(
            Metadata\Source::class,
            $dependencies['aliases'][MetadataInterface::class]
        );
    }

    public function testGetDependenciesContainsMetadataFactory(): void
    {
        $dependencies = $this->configProvider->getDependencies();

        self::assertArrayHasKey(Metadata\Source::class, $dependencies['factories']);
        self::assertSame(
            Container\MetadataInterfaceFactory::class,
            $dependencies['factories'][Metadata\Source::class]
        );
    }

    public function testConfigReturnsCorrectStructure(): void
    {
        $config = $this->configProvider->getDependencies();

        self::assertArrayHasKey('aliases', $config);
        self::assertArrayHasKey('factories', $config);
    }

    public function testGetDependenciesContainsDriverAliases(): void
    {
        $config = $this->configProvider->getDependencies();

        $expectedAliases = [
            'SQLite'     => Driver::class,
            'Sqlite'     => Driver::class,
            'sqlite'     => Driver::class,
            'pdo'        => Driver::class,
            'pdo_sqlite' => Driver::class,
            'pdosqlite'  => Driver::class,
            'pdodriver'  => Driver::class,
        ];

        foreach ($expectedAliases as $alias => $target) {
            self::assertArrayHasKey($alias, $config['aliases']);
            self::assertSame($target, $config['aliases'][$alias]);
        }
    }

    public function testGetDependenciesContainsInterfaceAliases(): void
    {
        $config = $this->configProvider->getDependencies();

        $expectedAliases = [
            ConnectionInterface::class          => Connection::class,
            PdoConnectionInterface::class       => Connection::class,
            DriverInterface::class              => Driver::class,
            PdoDriverInterface::class           => Driver::class,
            PlatformInterface::class            => AdapterPlatform::class,
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

    public function testConfigContainsFactories(): void
    {
        $config = $this->configProvider->getDependencies();

        $expectedFactories = [
            AdapterInterface::class    => Container\AdapterInterfaceFactory::class,
            Connection::class          => Container\PdoConnectionFactory::class,
            Driver::class              => Container\PdoDriverFactory::class,
            Result::class              => Container\PdoResultFactory::class,
            Statement::class           => Container\PdoStatementFactory::class,
            AdapterPlatform::class     => Container\PlatformInterfaceFactory::class,
            Profiler::class            => InvokableFactory::class,
            ResultSet\ResultSet::class => InvokableFactory::class,
        ];

        foreach ($expectedFactories as $service => $factory) {
            self::assertArrayHasKey($service, $config['factories']);
            self::assertSame($factory, $config['factories'][$service]);
        }
    }
}
