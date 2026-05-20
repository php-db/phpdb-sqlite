<?php

declare(strict_types=1);

namespace PhpDbTest\Sqlite\Container;

use PhpDb\Adapter\Exception\InvalidConnectionParametersException;
use PhpDb\Sqlite\Container\PdoConnectionFactory;
use PhpDb\Sqlite\Pdo\Connection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

#[CoversClass(PdoConnectionFactory::class)]
final class PdoConnectionFactoryTest extends TestCase
{
    public function testInvokeReturnsConnection(): void
    {
        $containerMock = $this->createMock(ContainerInterface::class);

        $factory    = new PdoConnectionFactory();
        $connection = $factory($containerMock, Connection::class, ['connection' => ['dsn' => 'sqlite::memory:']]);

        self::assertInstanceOf(Connection::class, $connection);
    }

    public function testInvokeWithoutConnectionConfigThrows(): void
    {
        $containerMock = $this->createMock(ContainerInterface::class);

        $this->expectException(InvalidConnectionParametersException::class);

        $factory = new PdoConnectionFactory();
        $factory($containerMock, Connection::class, []);
    }

    public function testInvokeWithNullOptionsThrows(): void
    {
        $containerMock = $this->createMock(ContainerInterface::class);

        $this->expectException(InvalidConnectionParametersException::class);

        $factory = new PdoConnectionFactory();
        $factory($containerMock, Connection::class, null);
    }
}
