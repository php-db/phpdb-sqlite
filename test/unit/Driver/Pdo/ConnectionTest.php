<?php

declare(strict_types=1);

namespace PhpDbTest\Adapter\Sqlite\Driver\Pdo;

use Exception;
use Override;
use PhpDb\Adapter\Exception\InvalidConnectionParametersException;
use PhpDb\Adapter\Sqlite\Driver\Pdo\Connection;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

use function realpath;

#[CoversMethod(Connection::class, 'getResource')]
#[CoversMethod(Connection::class, 'getDsn')]
final class ConnectionTest extends TestCase
{
    protected Connection $connection;
    //protected string $dsn = 'sqlite::memory:';
    protected string $dsn = 'sqlite::memory:';

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    #[Override]
    protected function setUp(): void
    {
        $this->connection = new Connection([
            'dsn' => $this->dsn,
        ]);
    }

    /**
     * Test getResource method tries to connect to  the database, it should never return null
     */
    public function testResource(): void
    {
        $this->expectNotToPerformAssertions();
        $this->connection->getResource();
    }

    /**
     * Test getConnectedDsn returns a DSN string if it has been set
     */
    public function testGetDsn(): void
    {
        $this->connection->setConnectionParameters(['dsn' => $this->dsn]);
        try {
            $this->connection->connect();
        } catch (Exception) {
        }
        $responseString = $this->connection->getDsn();

        self::assertEquals($this->dsn, $responseString);
    }

    #[Group('2622')]
    public function testArrayOfConnectionParametersCreatesCorrectDsn(): void
    {
        $this->connection->setConnectionParameters([
            'dsn' => 'sqlite::memory:',
        ]);
        try {
            $this->connection->connect();
        } catch (Exception) {
        }
        $responseString = $this->connection->getDsn();

        self::assertStringStartsWith('sqlite:', $responseString);
        self::assertStringContainsString('memory', $responseString);
    }
}
