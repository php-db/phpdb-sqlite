<?php

declare(strict_types=1);

namespace PhpDbTest\Adapter\Sqlite\Sql\Platform;

use PhpDb\Adapter\Sqlite\Sql\Platform\Ddl\AlterTableDecorator;
use PhpDb\Adapter\Sqlite\Sql\Platform\Ddl\CreateTableDecorator;
use PhpDb\Adapter\Sqlite\Sql\Platform\SelectDecorator;
use PhpDb\Adapter\Sqlite\Sql\Platform\Sqlite;
use PhpDb\Sql\Ddl\AlterTable;
use PhpDb\Sql\Ddl\CreateTable;
use PhpDb\Sql\Select;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

#[CoversClass(Sqlite::class)]
final class SqliteTest extends TestCase
{
    private Sqlite $platform;

    protected function setUp(): void
    {
        $this->platform = new Sqlite();
    }

    public function testConstructorSetsTypeDecorators(): void
    {
        self::assertInstanceOf(Sqlite::class, $this->platform);
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
