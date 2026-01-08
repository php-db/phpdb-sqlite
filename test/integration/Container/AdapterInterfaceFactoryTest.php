<?php

declare(strict_types=1);

namespace PhpDbIntegrationTest\Adapter\Sqlite\Container;

use PhpDb\Adapter\AdapterInterface;
use PhpDb\Adapter\Sqlite\Container\AdapterInterfaceFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

#[CoversClass(AdapterInterfaceFactory::class)]
#[CoversMethod(AdapterInterfaceFactory::class, '__invoke')]
final class AdapterInterfaceFactoryTest extends TestCase
{
    use TestAsset\SetupTrait;

    public function testFactoryReturnsAdapterInterface(): void
    {
        $factory = new AdapterInterfaceFactory();
        $adapter = $factory($this->container);
        self::assertInstanceOf(AdapterInterface::class, $adapter);
    }
}
