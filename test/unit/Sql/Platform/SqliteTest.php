<?php

namespace LaminasTest\Db\Sqlite\Sql\Platform;

use Laminas\Db\Sql\Select;
use Laminas\Db\Sqlite\Sql\Platform\SelectDecorator;
use Laminas\Db\Sqlite\Sql\Platform\Sqlite;
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
