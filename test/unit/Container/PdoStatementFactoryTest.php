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
    public function testInvokeReturnsStatementWithOptions(): void
    {
        $containerMock = $this->createMock(ContainerInterface::class);

        $factory   = new PdoStatementFactory();
        $statement = $factory($containerMock, Statement::class, ['key' => 'value']);

        self::assertInstanceOf(Statement::class, $statement);
    }

    public function testInvokeReturnsStatementWithEmptyOptions(): void
    {
        $containerMock = $this->createMock(ContainerInterface::class);

        $factory   = new PdoStatementFactory();
        $statement = $factory($containerMock, Statement::class, []);

        self::assertInstanceOf(Statement::class, $statement);
    }
}
