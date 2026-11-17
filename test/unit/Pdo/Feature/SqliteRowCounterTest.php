<?php

declare(strict_types=1);

namespace PhpDbTest\Sqlite\Pdo\Feature;

use Override;
use PDO;
use PDOStatement;
use PhpDb\Adapter\Driver\ConnectionInterface;
use PhpDb\Adapter\Driver\DriverInterface;
use PhpDb\Adapter\Driver\Pdo\Statement;
use PhpDb\Adapter\Driver\ResultInterface;
use PhpDb\Adapter\Exception\RuntimeException;
use PhpDb\Sqlite\Pdo\Feature\SqliteRowCounter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(SqliteRowCounter::class)]
#[Group('unit')]
final class SqliteRowCounterTest extends TestCase
{
    private SqliteRowCounter $rowCounter;

    /** @return array<string, array{0: string}> */
    public static function nonSelectSqlProvider(): array
    {
        return [
            'empty string'     => [''],
            'insert statement' => ['INSERT INTO foo VALUES (1)'],
        ];
    }

    /** @return array<string, array{0: string|null}> */
    public static function nonSelectStatementSqlProvider(): array
    {
        return [
            'no sql'           => [null],
            'insert statement' => ['INSERT INTO foo VALUES (1)'],
        ];
    }

    #[DataProvider('nonSelectSqlProvider')]
    #[Test]
    public function countForSqlIsZeroWhenSqlIsNotASelect(string $sql): void
    {
        static::assertSame(0, $this->rowCounter->getCountForSql($sql));
    }

    #[Test]
    public function countForSqlThrowsWhenTheCountQueryFails(): void
    {
        $pdo = $this->createStub(PDO::class);
        $pdo->method('query')->willReturn(false);
        $this->rowCounter->setDriver($this->createDriverForPdo($pdo));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('the count query did not produce a statement');

        $this->rowCounter->getCountForSql('SELECT * FROM foo');
    }

    #[Test]
    public function countForSqlWrapsTheSelectInACountQuery(): void
    {
        $pdo = $this->createMock(PDO::class);
        $pdo->expects(self::once())
            ->method('query')
            ->with('SELECT COUNT(*) as count FROM (SELECT * FROM foo)')
            ->willReturn($this->createCountStatement(4));
        $this->rowCounter->setDriver($this->createDriverForPdo($pdo));

        static::assertSame(4, $this->rowCounter->getCountForSql('SELECT * FROM foo'));
    }

    #[Test]
    public function countForStatementFetchesTheCountFromTheExecutedResult(): void
    {
        $statement = $this->createStatementReturningResource($this->createCountStatement(7));

        static::assertSame(7, $this->rowCounter->getCountForStatement($statement));
    }

    #[DataProvider('nonSelectStatementSqlProvider')]
    #[Test]
    public function countForStatementIsZeroWhenSqlIsNotASelect(?string $sql): void
    {
        $statement = $this->createStub(Statement::class);
        $statement->method('getSql')->willReturn($sql);

        static::assertSame(0, $this->rowCounter->getCountForStatement($statement));
    }

    #[Test]
    public function countForStatementThrowsWhenTheResultHasNoPdoStatement(): void
    {
        $statement = $this->createStatementReturningResource(null);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('the count query did not produce a statement');

        $this->rowCounter->getCountForStatement($statement);
    }

    #[Test]
    public function rowCountClosureCountsAnSqlContext(): void
    {
        $pdo = $this->createStub(PDO::class);
        $pdo->method('query')->willReturn($this->createCountStatement(3));
        $this->rowCounter->setDriver($this->createDriverForPdo($pdo));

        static::assertSame(3, $this->rowCounter->getRowCountClosure('SELECT * FROM foo')());
    }

    #[Test]
    public function rowCountClosureCountsAStatementContext(): void
    {
        $statement = $this->createStatementReturningResource($this->createCountStatement(2));

        static::assertSame(2, $this->rowCounter->getRowCountClosure($statement)());
    }

    #[Override]
    protected function setUp(): void
    {
        $this->rowCounter = new SqliteRowCounter();
    }

    private function createCountStatement(int $count): PDOStatement
    {
        $countStatement = $this->createStub(PDOStatement::class);
        $countStatement->method('fetch')->willReturn(['count' => (string) $count]);

        return $countStatement;
    }

    private function createDriverForPdo(PDO $pdo): DriverInterface
    {
        $connection = $this->createStub(ConnectionInterface::class);
        $connection->method('getResource')->willReturn($pdo);

        $driver = $this->createStub(DriverInterface::class);
        $driver->method('getConnection')->willReturn($connection);

        return $driver;
    }

    private function createStatementReturningResource(mixed $resource): Statement
    {
        $result = $this->createStub(ResultInterface::class);
        $result->method('getResource')->willReturn($resource);

        $statement = $this->createStub(Statement::class);
        $statement->method('getSql')->willReturn('SELECT * FROM foo');
        $statement->method('execute')->willReturn($result);

        return $statement;
    }
}
