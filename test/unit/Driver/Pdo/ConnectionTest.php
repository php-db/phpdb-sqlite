<?php

declare(strict_types=1);

namespace LaminasTest\Db\Sqlite\Driver\Pdo;

use Exception;
use Laminas\Db\Adapter\Exception\InvalidConnectionParametersException;
use Laminas\Db\Sqlite\Driver\Pdo\Connection;
use Laminas\Db\Sqlite\Driver\Pdo\Driver;
use Override;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[CoversMethod(Connection::class, 'getResource')]
#[CoversMethod(Connection::class, 'getDsn')]
final class ConnectionTest extends TestCase
{
    protected Connection $connection;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    #[Override]
    protected function setUp(): void
    {
        $this->connection = new Connection();
    }

    /**
     * Test getResource method tries to connect to  the database, it should never return null
     */
    public function testResource(): void
    {
        $this->expectException(InvalidConnectionParametersException::class);
        $this->connection->getResource();
    }

    /**
     * Test getConnectedDsn returns a DSN string if it has been set
     */
    public function testGetDsn(): void
    {
        $dsn = "sqlite::memory:";
        $this->connection->setConnectionParameters(['dsn' => $dsn]);
        try {
            $this->connection->connect();
        } catch (Exception) {
        }
        $responseString = $this->connection->getDsn();

        self::assertEquals($dsn, $responseString);
    }

    #[Group('2622')]
    public function testArrayOfConnectionParametersCreatesCorrectDsn(): void
    {
        $this->connection->setConnectionParameters([
            'driver'   => 'Pdo_Sqlite',
            'database' => ':memory',
        ]);
        try {
            $this->connection->connect();
        } catch (Exception) {
        }
        $responseString = $this->connection->getDsn();

        self::assertStringStartsWith('sqlite:', $responseString);
        self::assertStringContainsString('memory', $responseString);
    }

    public function testDblibArrayOfConnectionParametersCreatesCorrectDsn(): void
    {
        $this->connection->setConnectionParameters([
            'driver'  => 'pdo_dblib',
            'charset' => 'UTF-8',
            'dbname'  => 'foo',
            'port'    => '1433',
            'version' => '7.3',
        ]);
        try {
            $this->connection->connect();
        } catch (Exception) {
        }
        $responseString = $this->connection->getDsn();

        $this->assertStringStartsWith('dblib:', $responseString);
        $this->assertStringContainsString('foo', $responseString);
    }
}
