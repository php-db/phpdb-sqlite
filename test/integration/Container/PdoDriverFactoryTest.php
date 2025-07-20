<?php

declare(strict_types=1);

namespace PhpDbIntegrationTest\Adapter\Sqlite\Container;

use PhpDb\Adapter\Driver\PdoDriverInterface;
use PhpDb\Adapter\Sqlite\Container\PdoDriverFactory;
use PhpDb\Adapter\Sqlite\Driver\Pdo\Pdo;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('container')]
#[Group('integration')]
#[CoversClass(PdoDriverFactory::class)]
#[CoversMethod(PdoDriverFactory::class, '__invoke')]
final class PdoDriverFactoryTest extends TestCase
{
    use TestAsset\SetupTrait;

    public function testInvokeReturnsPdoDriver(): void
    {
        $factory  = new PdoDriverFactory();
        $instance = $factory($this->container);

        self::assertInstanceOf(PdoDriverInterface::class, $instance);
        self::assertInstanceOf(Pdo::class, $instance);
    }
}
