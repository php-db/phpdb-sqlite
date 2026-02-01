<?php

declare(strict_types=1);

namespace PhpDbTest\Sqlite\Sql\Ddl;

use PhpDb\Sql\Ddl\CreateTable;
use PhpDb\Sqlite\Sql\Ddl\CreateTableDecorator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

use function strlen;

#[CoversClass(CreateTableDecorator::class)]
final class CreateTableDecoratorTest extends TestCase
{
    private CreateTableDecorator $decorator;

    protected function setUp(): void
    {
        $this->decorator = new CreateTableDecorator();
    }

    public function testSetSubject(): void
    {
        $createTable = new CreateTable('test_table');
        $result      = $this->decorator->setSubject($createTable);

        self::assertSame($this->decorator, $result);

        $reflection      = new ReflectionClass($this->decorator);
        $subjectProperty = $reflection->getProperty('subject');
        $subject         = $subjectProperty->getValue($this->decorator);

        self::assertSame($createTable, $subject);
    }

    public function testColumnOptionSortOrder(): void
    {
        $reflection        = new ReflectionClass($this->decorator);
        $sortOrderProperty = $reflection->getProperty('columnOptionSortOrder');
        $sortOrder         = $sortOrderProperty->getValue($this->decorator);

        self::assertIsArray($sortOrder);
        self::assertArrayHasKey('unsigned', $sortOrder);
        self::assertArrayHasKey('zerofill', $sortOrder);
        self::assertArrayHasKey('identity', $sortOrder);
        self::assertArrayHasKey('serial', $sortOrder);
        self::assertArrayHasKey('autoincrement', $sortOrder);
        self::assertArrayHasKey('comment', $sortOrder);
        self::assertArrayHasKey('columnformat', $sortOrder);
        self::assertArrayHasKey('format', $sortOrder);
        self::assertArrayHasKey('storage', $sortOrder);
    }

    public function testGetSqlInsertOffsets(): void
    {
        $sql = 'column_name VARCHAR(255) NOT NULL DEFAULT "test" UNIQUE PRIMARY REFERENCES other_table(id)';

        $reflection = new ReflectionClass($this->decorator);
        $method     = $reflection->getMethod('getSqlInsertOffsets');

        $offsets = $method->invoke($this->decorator, $sql);

        self::assertIsArray($offsets);
        self::assertCount(4, $offsets);
        self::assertIsInt($offsets[0]);
        self::assertIsInt($offsets[1]);
        self::assertIsInt($offsets[2]);
        self::assertIsInt($offsets[3]);
    }

    public function testGetSqlInsertOffsetsWithNullKeyword(): void
    {
        $sql = 'column_name VARCHAR(255) NULL';

        $reflection = new ReflectionClass($this->decorator);
        $method     = $reflection->getMethod('getSqlInsertOffsets');

        $offsets = $method->invoke($this->decorator, $sql);

        self::assertIsArray($offsets);
        self::assertGreaterThan(0, $offsets[0]);
    }

    public function testGetSqlInsertOffsetsWithDefaultKeyword(): void
    {
        $sql = 'column_name INTEGER DEFAULT 42';

        $reflection = new ReflectionClass($this->decorator);
        $method     = $reflection->getMethod('getSqlInsertOffsets');

        $offsets = $method->invoke($this->decorator, $sql);

        self::assertIsArray($offsets);
        self::assertGreaterThan(0, $offsets[0]);
    }

    public function testGetSqlInsertOffsetsWithUniqueKeyword(): void
    {
        $sql = 'column_name VARCHAR(100) UNIQUE';

        $reflection = new ReflectionClass($this->decorator);
        $method     = $reflection->getMethod('getSqlInsertOffsets');

        $offsets = $method->invoke($this->decorator, $sql);

        self::assertIsArray($offsets);
        self::assertGreaterThan(0, $offsets[1]);
    }

    public function testGetSqlInsertOffsetsWithPrimaryKeyword(): void
    {
        $sql = 'id INTEGER PRIMARY KEY';

        $reflection = new ReflectionClass($this->decorator);
        $method     = $reflection->getMethod('getSqlInsertOffsets');

        $offsets = $method->invoke($this->decorator, $sql);

        self::assertIsArray($offsets);
        self::assertGreaterThan(0, $offsets[1]);
    }

    public function testGetSqlInsertOffsetsWithReferencesKeyword(): void
    {
        $sql = 'user_id INTEGER REFERENCES users(id)';

        $reflection = new ReflectionClass($this->decorator);
        $method     = $reflection->getMethod('getSqlInsertOffsets');

        $offsets = $method->invoke($this->decorator, $sql);

        self::assertIsArray($offsets);
        self::assertGreaterThan(0, $offsets[2]);
    }

    public function testGetSqlInsertOffsetsWithoutKeywords(): void
    {
        $sql = 'column_name VARCHAR(255)';

        $reflection = new ReflectionClass($this->decorator);
        $method     = $reflection->getMethod('getSqlInsertOffsets');

        $offsets = $method->invoke($this->decorator, $sql);

        self::assertIsArray($offsets);
        $sqlLength = strlen($sql);
        self::assertSame($sqlLength, $offsets[0]);
        self::assertSame($sqlLength, $offsets[1]);
        self::assertSame($sqlLength, $offsets[2]);
        self::assertSame($sqlLength, $offsets[3]);
    }

    public function testNormalizeColumnOption(): void
    {
        $reflection = new ReflectionClass($this->decorator);
        $method     = $reflection->getMethod('normalizeColumnOption');

        self::assertSame('autoincrement', $method->invoke($this->decorator, 'auto-increment'));
        self::assertSame('autoincrement', $method->invoke($this->decorator, 'auto_increment'));
        self::assertSame('autoincrement', $method->invoke($this->decorator, 'AUTO INCREMENT'));
        self::assertSame('columnformat', $method->invoke($this->decorator, 'column-format'));
        self::assertSame('zerofill', $method->invoke($this->decorator, 'ZERO_FILL'));
    }

    public function testCompareColumnOptions(): void
    {
        $reflection = new ReflectionClass($this->decorator);
        $method     = $reflection->getMethod('compareColumnOptions');

        // unsigned (0) should come before zerofill (1)
        $result = $method->invoke($this->decorator, 'unsigned', 'zerofill');
        self::assertLessThan(0, $result);

        // zerofill (1) should come before identity (2)
        $result = $method->invoke($this->decorator, 'zerofill', 'identity');
        self::assertLessThan(0, $result);

        // identity (2) should come before comment (3)
        $result = $method->invoke($this->decorator, 'identity', 'comment');
        self::assertLessThan(0, $result);

        // comment (3) should come before columnformat (4)
        $result = $method->invoke($this->decorator, 'comment', 'columnformat');
        self::assertLessThan(0, $result);

        // storage (5) should come after format (4)
        $result = $method->invoke($this->decorator, 'format', 'storage');
        self::assertLessThan(0, $result);
    }

    public function testCompareColumnOptionsWithSameValue(): void
    {
        $reflection = new ReflectionClass($this->decorator);
        $method     = $reflection->getMethod('compareColumnOptions');

        $result = $method->invoke($this->decorator, 'unsigned', 'unsigned');
        self::assertSame(0, $result);
    }

    public function testCompareColumnOptionsWithUnknownOptions(): void
    {
        $reflection = new ReflectionClass($this->decorator);
        $method     = $reflection->getMethod('compareColumnOptions');

        // Unknown options should be treated equally
        $result = $method->invoke($this->decorator, 'unknown1', 'unknown2');
        self::assertSame(0, $result);
    }

    public function testCompareColumnOptionsKnownVsUnknown(): void
    {
        $reflection = new ReflectionClass($this->decorator);
        $method     = $reflection->getMethod('compareColumnOptions');

        // Known option should come before unknown option
        $result = $method->invoke($this->decorator, 'unsigned', 'unknown');
        self::assertLessThan(0, $result);

        // Unknown option should come after known option
        $result = $method->invoke($this->decorator, 'unknown', 'unsigned');
        self::assertGreaterThan(0, $result);
    }
}
