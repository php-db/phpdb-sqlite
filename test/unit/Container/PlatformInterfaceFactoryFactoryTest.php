<?php

declare(strict_types=1);

namespace PhpDbTest\Adapter\Sqlite\Container;

use PhpDb\Adapter\Sqlite\Container\PlatformInterfaceFactory;
use PhpDb\Adapter\Sqlite\Container\PlatformInterfaceFactoryFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PlatformInterfaceFactoryFactory::class)]
final class PlatformInterfaceFactoryFactoryTest extends TestCase
{
    public function testInvokeReturnsCallable(): void
    {
        $factoryFactory = new PlatformInterfaceFactoryFactory();
        $result         = $factoryFactory();

        self::assertIsCallable($result);
    }

    public function testInvokeReturnsPlatformInterfaceFactory(): void
    {
        $factoryFactory = new PlatformInterfaceFactoryFactory();
        $result         = $factoryFactory();

        self::assertInstanceOf(PlatformInterfaceFactory::class, $result);
    }
}
