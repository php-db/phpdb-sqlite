<?php

declare(strict_types=1);

namespace PhpDbTest\Sqlite\Container;

use PhpDb\Adapter\Driver\Pdo\Statement;
use PhpDb\Sqlite\Container\PdoStatementFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

#[CoversClass(PdoStatementFactory::class)]
final class PdoStatementFactoryTest extends TestCase
{
    public function testInvokeReturnsStatement(): void
    {
        $containerMock = $this->createMock(ContainerInterface::class);
        $containerMock->method('get')
            ->with('config')
            ->willReturn([
                'db' => [
                    'options' => [],
                ],
            ]);

        $factory   = new PdoStatementFactory();
        $statement = $factory($containerMock);

        self::assertInstanceOf(Statement::class, $statement);
    }

    public function testInvokeWithoutOptionsConfig(): void
    {
        $containerMock = $this->createMock(ContainerInterface::class);
        $containerMock->method('get')
            ->with('config')
            ->willReturn([
                'db' => [],
            ]);

        $factory   = new PdoStatementFactory();
        $statement = $factory($containerMock);

        self::assertInstanceOf(Statement::class, $statement);
    }

    public function testInvokeWithoutDbConfig(): void
    {
        $containerMock = $this->createMock(ContainerInterface::class);
        $containerMock->method('get')
            ->with('config')
            ->willReturn([]);

        $factory   = new PdoStatementFactory();
        $statement = $factory($containerMock);

        self::assertInstanceOf(Statement::class, $statement);
    }
}
