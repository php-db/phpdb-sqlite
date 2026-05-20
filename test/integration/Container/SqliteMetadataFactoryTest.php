<?php

declare(strict_types=1);

namespace PhpDbIntegrationTest\Sqlite\Container;

use PhpDb\Metadata\MetadataInterface;
use PhpDb\Sqlite\Container\MetadataInterfaceFactory;
use PhpDb\Sqlite\Metadata;
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
        $metadata = $factory($this->container, Metadata\Source::class);
        self::assertInstanceOf(MetadataInterface::class, $metadata);
        self::assertInstanceOf(Metadata\Source::class, $metadata);
    }
}
