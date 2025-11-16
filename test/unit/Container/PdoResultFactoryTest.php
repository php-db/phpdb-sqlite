<?php

declare(strict_types=1);

namespace PhpDbTest\Adapter\Sqlite\Container;

use PhpDb\Adapter\Driver\Pdo\Result;
use PhpDb\Adapter\Sqlite\Container\PdoResultFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

#[CoversClass(PdoResultFactory::class)]
final class PdoResultFactoryTest extends TestCase
{
    public function testInvokeReturnsResult(): void
    {
        $containerMock = $this->createMock(ContainerInterface::class);

        $factory = new PdoResultFactory();
        $result  = $factory($containerMock);

        self::assertInstanceOf(Result::class, $result);
    }
}
