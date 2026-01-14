<?php

declare(strict_types=1);

namespace PhpDbTest\Adapter\Sqlite;

use PhpDb\Adapter\Driver\Pdo\Result;
use PhpDb\Adapter\Driver\Pdo\Statement;
use PhpDb\Adapter\Driver\PdoConnectionInterface;
use PhpDb\Adapter\Driver\PdoDriverInterface;
use PhpDb\Adapter\Driver\ResultInterface;
use PhpDb\Adapter\Driver\StatementInterface;
use PhpDb\Adapter\Platform\PlatformInterface;
use PhpDb\Adapter\Sqlite\AdapterPlatform;
use PhpDb\Adapter\Sqlite\ConfigProvider;
use PhpDb\Adapter\Sqlite\Container;
use PhpDb\Adapter\Sqlite\Metadata;
use PhpDb\Adapter\Sqlite\Pdo;
use PhpDb\Metadata\MetadataInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ConfigProvider::class)]
final class ConfigProviderTest extends TestCase
{
    public const EXPECTED_ALIASES = [
        'SQLite'                      => Pdo\Driver::class,
        'Sqlite'                      => Pdo\Driver::class,
        'sqlite'                      => Pdo\Driver::class,
        'pdo'                         => Pdo\Driver::class,
        'pdo_sqlite'                  => Pdo\Driver::class,
        'pdosqlite'                   => Pdo\Driver::class,
        'pdodriver'                   => Pdo\Driver::class,
        PdoConnectionInterface::class => Pdo\Connection::class,
        PdoDriverInterface::class     => Pdo\Driver::class,
        PlatformInterface::class      => AdapterPlatform::class,
        ResultInterface::class        => Result::class,
        StatementInterface::class     => Statement::class,
        MetadataInterface::class      => Metadata\Source::class,
    ];
    private ConfigProvider $configProvider;

    protected function setUp(): void
    {
        $this->configProvider = new ConfigProvider();
    }

    public function testInvokeReturnsExpectedStructure(): void
    {
        $config = ($this->configProvider)();

        self::assertNotEmpty($config);
        self::assertArrayHasKey('dependencies', $config);
    }

    public function testInvokeReturnsCorrectStructure(): void
    {
        $config = (new ConfigProvider())();
        self::assertArrayHasKey('dependencies', $config);
        self::assertArrayHasKey('aliases', $config['dependencies']);
        self::assertArrayHasKey('factories', $config['dependencies']);
    }

    public function testGetDependenciesReturnsCorrectStructure(): void
    {
        $dependencies = $this->configProvider->getDependencies();

        self::assertNotEmpty($dependencies);
        self::assertArrayHasKey('aliases', $dependencies);
        self::assertArrayHasKey('factories', $dependencies);
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

    public function testGetDependenciesContainsExpectedAliases(): void
    {
        $config = $this->configProvider->getDependencies();
        self::assertEquals(self::EXPECTED_ALIASES, $config['aliases']);
    }
}
