<?php

declare(strict_types=1);

namespace PhpDbIntegrationTest\Adapter\Sqlite\Container;

use PhpDb\Adapter\Sqlite\Container\MetadataInterfaceFactory;
use PhpDb\Adapter\Sqlite\Metadata\Source\SqliteMetadata;
use PhpDb\Metadata\MetadataInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

#[CoversClass(MetadataInterfaceFactory::class)]
#[CoversMethod(MetadataInterfaceFactory::class, '__invoke')]
final class SqliteMetadataFactoryTest extends TestCase
{
    use TestAsset\SetupTrait;

    public function testFactoryReturnsMysqlMetadata(): void
    {
        $factory  = new MetadataInterfaceFactory();
        $metadata = $factory($this->container);
        self::assertInstanceOf(MetadataInterface::class, $metadata);
        self::assertInstanceOf(SqliteMetadata::class, $metadata);
    }
}
