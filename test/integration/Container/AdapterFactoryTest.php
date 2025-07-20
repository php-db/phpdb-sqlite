<?php

declare(strict_types=1);

namespace PhpDbIntegrationTest\Adapter\Sqlite\Container;

use PhpDb\Adapter\AdapterInterface;
use PhpDb\Adapter\Sqlite\Container\AdapterFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

#[CoversClass(AdapterFactory::class)]
#[CoversMethod(AdapterFactory::class, '__invoke')]
final class AdapterFactoryTest extends TestCase
{
    use TestAsset\SetupTrait;

    public function testFactoryReturnsAdapterInterface(): void
    {
        $factory = new AdapterFactory();
        $adapter = $factory($this->container);
        self::assertInstanceOf(AdapterInterface::class, $adapter);
    }
}
