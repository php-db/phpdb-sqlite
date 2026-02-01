<?php

declare(strict_types=1);

namespace PhpDbTest\Sqlite\Container;

use PhpDb\Sqlite\Container\PdoDriverInterfaceFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PdoDriverInterfaceFactory::class)]
final class PdoDriverInterfaceFactoryTest extends TestCase
{
    public function testFactoryExists(): void
    {
        $factory = new PdoDriverInterfaceFactory();

        self::assertInstanceOf(PdoDriverInterfaceFactory::class, $factory);
    }
}
