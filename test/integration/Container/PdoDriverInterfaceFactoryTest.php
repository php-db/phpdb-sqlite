<?php

declare(strict_types=1);

namespace PhpDbIntegrationTest\Sqlite\Container;

use PhpDb\Adapter\AdapterInterface;
use PhpDb\Adapter\Driver\PdoDriverInterface;
use PhpDb\Sqlite\Container\PdoDriverInterfaceFactory;
use PhpDb\Sqlite\Pdo\Driver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('container')]
#[Group('integration')]
#[CoversClass(PdoDriverInterfaceFactory::class)]
#[CoversMethod(PdoDriverInterfaceFactory::class, '__invoke')]
final class PdoDriverInterfaceFactoryTest extends TestCase
{
    use TestAsset\SetupTrait;

    public function testInvokeReturnsPdoDriver(): void
    {
        $factory  = new PdoDriverInterfaceFactory();
        $instance = $factory(
            $this->container,
            Driver::class,
            $this->container->get('config')[AdapterInterface::class]
        );

        self::assertInstanceOf(PdoDriverInterface::class, $instance);
        self::assertInstanceOf(Driver::class, $instance);
    }
}
