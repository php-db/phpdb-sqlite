<?php

declare(strict_types=1);

namespace PhpDbIntegrationTest\Sqlite\Pdo;

use PDO;
use PDOStatement;
use PhpDb\Adapter\Driver\Pdo\Result;
use PhpDb\Adapter\Driver\Pdo\Statement;
use PhpDb\Adapter\Driver\StatementInterface;
use PhpDbIntegrationTest\Sqlite\Container\TestAsset\SetupTrait;
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
    use SetupTrait;

    public function testGetResource(): void
    {
        /** @var PDO $pdo */
        $pdo = $this->getAdapter()->getDriver()->getConnection()->getResource();
        /** @var StatementInterface&Statement $statement */
        $statement = $this->getAdapter()->getDriver()->createStatement();
        /** @var PDOStatement $stmt */
        $stmt = $pdo->prepare('SELECT 1');
        $statement->setResource($stmt);

        self::assertSame($stmt, $statement->getResource());
    }

    public function testSetSql(): void
    {
        /** @var StatementInterface&Statement $statement */
        $statement = $this->getAdapter()->getDriver()->createStatement();
        $statement->setSql('SELECT 1');
        self::assertEquals('SELECT 1', $statement->getSql());
    }

    public function testGetSql(): void
    {
        $statement = $this->getAdapter()->getDriver()->createStatement();
        $statement->setSql('SELECT 1');
        self::assertEquals('SELECT 1', $statement->getSql());
    }

    /**
     * @todo Implement testPrepare().
     */
    public function testPrepare(): void
    {
        /** @var StatementInterface&Statement $statement */
        $statement = $this->getAdapter()->getDriver()->createStatement();
        self::assertInstanceOf(StatementInterface::class, $statement->prepare('SELECT 1'));
    }

    public function testIsPrepared(): void
    {
        /** @var StatementInterface&Statement $statement */
        $statement = $this->getAdapter()->getDriver()->createStatement();
        self::assertFalse($statement->isPrepared());
        //$statement->initialize($resource);
        $statement->prepare('SELECT 1');
        self::assertTrue($statement->isPrepared());
    }

    public function testExecute(): void
    {
        /** @var StatementInterface&Statement $statement */
        $statement = $this->getAdapter()->getDriver()->createStatement();
        //$statement->initialize($pdo);
        $statement->prepare('SELECT 1');
        self::assertInstanceOf(Result::class, $statement->execute());
    }
}
