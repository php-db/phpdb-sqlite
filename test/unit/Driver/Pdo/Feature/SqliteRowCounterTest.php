<?php

declare(strict_types=1);

namespace PhpDbTest\Adapter\Sqlite\Driver\Pdo\Feature;

use PhpDb\Adapter\Sqlite\Driver\Pdo\Feature\SqliteRowCounter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SqliteRowCounter::class)]
final class SqliteRowCounterTest extends TestCase
{
    private SqliteRowCounter $rowCounter;

    protected function setUp(): void
    {
        $this->rowCounter = new SqliteRowCounter();
    }

    public function testRowCounterExists(): void
    {
        self::assertInstanceOf(SqliteRowCounter::class, $this->rowCounter);
    }
}
