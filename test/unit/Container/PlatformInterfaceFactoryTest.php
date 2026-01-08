<?php

declare(strict_types=1);

namespace PhpDbTest\Adapter\Sqlite\Container;

use PhpDb\Adapter\Driver\PdoDriverInterface;
use PhpDb\Adapter\Sqlite\AdapterPlatform;
use PhpDb\Adapter\Sqlite\Container\PlatformInterfaceFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PlatformInterfaceFactory::class)]
final class PlatformInterfaceFactoryTest extends TestCase
{
    public function testFromDriverReturnsPlatform(): void
    {
        $driverMock = $this->createMock(PdoDriverInterface::class);

        $platform = PlatformInterfaceFactory::fromDriver($driverMock);

        self::assertInstanceOf(AdapterPlatform::class, $platform);
    }
}
