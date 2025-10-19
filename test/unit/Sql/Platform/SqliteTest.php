<?php

namespace PhpDbTest\Adapter\Sqlite\Sql\Platform;

use PhpDb\Adapter\Sqlite\Sql\Platform\SelectDecorator;
use PhpDb\Adapter\Sqlite\Sql\Platform\Sqlite;
use PhpDb\Sql\Select;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

use function current;
use function key;

#[CoversMethod(Sqlite::class, '__construct')]
final class SqliteTest extends TestCase
{
    #[TestDox('unit test / object test: Test Sqlite constructor will register the decorator')]
    public function testConstructorRegistersSqliteDecorator(): void
    {
        $mysql      = new Sqlite();
        $decorators = $mysql->getDecorators();

        $type      = key($decorators);
        $decorator = current($decorators);
        self::assertEquals(Select::class, $type);
        self::assertInstanceOf(SelectDecorator::class, $decorator);
    }
}
