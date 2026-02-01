<?php

declare(strict_types=1);

namespace PhpDbTest\Sqlite\Sql;

use PhpDb\Sql\Ddl\AlterTable;
use PhpDb\Sql\Ddl\CreateTable;
use PhpDb\Sql\Select;
use PhpDb\Sqlite\Sql\Ddl\AlterTableDecorator;
use PhpDb\Sqlite\Sql\Ddl\CreateTableDecorator;
use PhpDb\Sqlite\Sql\Platform;
use PhpDb\Sqlite\Sql\SelectDecorator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

#[CoversClass(Platform::class)]
final class PlatformTest extends TestCase
{
    private Platform $platform;

    protected function setUp(): void
    {
        $this->platform = new Platform();
    }

    public function testConstructorSetsTypeDecorators(): void
    {
        self::assertInstanceOf(Platform::class, $this->platform);
    }

    public function testSelectDecoratorIsRegistered(): void
    {
        $reflection         = new ReflectionClass($this->platform);
        $decoratorsProperty = $reflection->getProperty('decorators');
        $decorators         = $decoratorsProperty->getValue($this->platform);

        self::assertArrayHasKey(Select::class, $decorators);
        self::assertInstanceOf(SelectDecorator::class, $decorators[Select::class]);
    }

    public function testCreateTableDecoratorIsRegistered(): void
    {
        $reflection         = new ReflectionClass($this->platform);
        $decoratorsProperty = $reflection->getProperty('decorators');
        $decorators         = $decoratorsProperty->getValue($this->platform);

        self::assertArrayHasKey(CreateTable::class, $decorators);
        self::assertInstanceOf(CreateTableDecorator::class, $decorators[CreateTable::class]);
    }

    public function testAlterTableDecoratorIsRegistered(): void
    {
        $reflection         = new ReflectionClass($this->platform);
        $decoratorsProperty = $reflection->getProperty('decorators');
        $decorators         = $decoratorsProperty->getValue($this->platform);

        self::assertArrayHasKey(AlterTable::class, $decorators);
        self::assertInstanceOf(AlterTableDecorator::class, $decorators[AlterTable::class]);
    }
}
