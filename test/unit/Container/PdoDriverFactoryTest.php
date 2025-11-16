<?php

declare(strict_types=1);

namespace PhpDbTest\Adapter\Sqlite\Container;

use PhpDb\Adapter\Sqlite\Container\PdoDriverFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PdoDriverFactory::class)]
final class PdoDriverFactoryTest extends TestCase
{
    public function testFactoryExists(): void
    {
        $factory = new PdoDriverFactory();

        self::assertInstanceOf(PdoDriverFactory::class, $factory);
    }
}
