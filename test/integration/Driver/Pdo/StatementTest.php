<?php

declare(strict_types=1);

namespace PhpDbIntegrationTest\Adapter\Sqlite\Driver\Pdo;

use PhpDb\Adapter\Driver\StatementInterface;
use PhpDb\Adapter\Driver\Pdo\Statement;
use PhpDb\Adapter\Sqlite\Driver\Pdo\Connection;
use PhpDb\Adapter\Sqlite\Driver\Pdo\Pdo;
use PhpDb\Adapter\Sqlite\Driver\Pdo\Result;
use PhpDbIntegrationTest\Adapter\Sqlite\Driver\Pdo\TestAsset\SqliteMemoryPdo;
use Override;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

#[CoversMethod(Statement::class, 'setDriver')]
#[CoversMethod(Statement::class, 'setParameterContainer')]
#[CoversMethod(Statement::class, 'getParameterContainer')]
#[CoversMethod(Statement::class, 'getResource')]
#[CoversMethod(Statement::class, 'setSql')]
#[CoversMethod(Statement::class, 'getSql')]
#[CoversMethod(Statement::class, 'prepare')]
#[CoversMethod(Statement::class, 'isPrepared')]
#[CoversMethod(Statement::class, 'execute')]
final class StatementTest extends TestCase
{

    protected Statement $statement;

    public function testGetResource(): void
    {
        $pdo  = new SqliteMemoryPdo();
        $stmt = $pdo->prepare('SELECT 1');
        $this->statement->setResource($stmt);

        self::assertSame($stmt, $this->statement->getResource());
    }

    public function testSetSql(): void
    {
        $this->statement->setSql('SELECT 1');
        self::assertEquals('SELECT 1', $this->statement->getSql());
    }

    public function testGetSql(): void
    {
        $this->statement->setSql('SELECT 1');
        self::assertEquals('SELECT 1', $this->statement->getSql());
    }

    /**
     * @todo Implement testPrepare().
     */
    public function testPrepare(): void
    {
        $this->statement->initialize(new SqliteMemoryPdo());
        self::assertInstanceOf(StatementInterface::class, $this->statement->prepare('SELECT 1'));
    }

    public function testIsPrepared(): void
    {
        self::assertFalse($this->statement->isPrepared());
        $this->statement->initialize(new SqliteMemoryPdo());
        $this->statement->prepare('SELECT 1');
        self::assertTrue($this->statement->isPrepared());
    }

    public function testExecute(): void
    {
        $this->statement->setDriver(new Driver(new Connection($pdo = new SqliteMemoryPdo())));
        $this->statement->initialize($pdo);
        $this->statement->prepare('SELECT 1');
        self::assertInstanceOf(Result::class, $this->statement->execute());
    }

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    #[Override]
    protected function setUp(): void
    {
        $this->statement = new Statement();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown(): void {}
}
