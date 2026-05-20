<?php

declare(strict_types=1);

namespace PhpDbTest\Sqlite\Container;

use PhpDb\Adapter\Adapter;
use PhpDb\Adapter\AdapterInterface;
use PhpDb\Adapter\Driver\PdoDriverInterface;
use PhpDb\Adapter\Platform\PlatformInterface;
use PhpDb\ResultSet\ResultSetInterface;
use PhpDb\Sqlite\Container\MetadataInterfaceFactory;
use PhpDb\Sqlite\Metadata;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

#[CoversClass(MetadataInterfaceFactory::class)]
final class MetadataInterfaceFactoryTest extends TestCase
{
    public function testInvokeReturnsMetadata(): void
    {
        $driverMock   = $this->createMock(PdoDriverInterface::class);
        $platformMock = $this->createMock(PlatformInterface::class);
        $platformMock->method('getName')->willReturn('SQLite');
        $resultSetMock = $this->createMock(ResultSetInterface::class);

        $adapterMock = new Adapter($driverMock, $platformMock, $resultSetMock);

        $containerMock = $this->createMock(ContainerInterface::class);
        $containerMock->method('get')
            ->with(AdapterInterface::class)
            ->willReturn($adapterMock);

        $factory  = new MetadataInterfaceFactory();
        $metadata = $factory($containerMock, Metadata\Source::class);

        self::assertInstanceOf(Metadata\Source::class, $metadata);
    }
}
