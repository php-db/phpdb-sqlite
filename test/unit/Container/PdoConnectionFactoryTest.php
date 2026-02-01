<?php

declare(strict_types=1);

namespace PhpDbTest\Sqlite\Container;

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
        $containerMock->method('get')
            ->with('config')
            ->willReturn([
                'db' => [
                    'connection' => [
                        'dsn' => 'sqlite::memory:',
                    ],
                ],
            ]);

        $factory    = new PdoConnectionFactory();
        $connection = $factory($containerMock);

        self::assertInstanceOf(Connection::class, $connection);
    }

    public function testInvokeWithoutConnectionConfig(): void
    {
        $containerMock = $this->createMock(ContainerInterface::class);
        $containerMock->method('get')
            ->with('config')
            ->willReturn([
                'db' => [],
            ]);

        $factory    = new PdoConnectionFactory();
        $connection = $factory($containerMock);

        self::assertInstanceOf(Connection::class, $connection);
    }

    public function testInvokeWithoutDbConfig(): void
    {
        $containerMock = $this->createMock(ContainerInterface::class);
        $containerMock->method('get')
            ->with('config')
            ->willReturn([]);

        $factory    = new PdoConnectionFactory();
        $connection = $factory($containerMock);

        self::assertInstanceOf(Connection::class, $connection);
    }
}
