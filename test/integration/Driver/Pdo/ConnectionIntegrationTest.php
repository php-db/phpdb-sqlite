<?php

declare(strict_types=1);

namespace PhpDbIntegrationTest\Adapter\Sqlite\Driver\Pdo;

use PDO;
use PhpDb\Adapter\Driver\PdoConnectionInterface;
use PhpDb\Adapter\Sqlite\Driver\Pdo\Connection;
use PhpDbIntegrationTest\Adapter\Sqlite\Container\TestAsset\SetupTrait;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[CoversMethod(Connection::class, 'getCurrentSchema')]
#[CoversMethod(Connection::class, 'setResource')]
#[CoversMethod(Connection::class, 'getResource')]
#[CoversMethod(Connection::class, 'connect')]
#[CoversMethod(Connection::class, 'isConnected')]
#[CoversMethod(Connection::class, 'disconnect')]
#[CoversMethod(Connection::class, 'beginTransaction')]
#[CoversMethod(Connection::class, 'commit')]
#[CoversMethod(Connection::class, 'rollback')]
#[CoversMethod(Connection::class, 'execute')]
#[CoversMethod(Connection::class, 'prepare')]
#[CoversMethod(Connection::class, 'getLastGeneratedValue')]
#[Group('integration')]
#[Group('integration-pdo')]
final class ConnectionIntegrationTest extends TestCase
{
    use SetupTrait;

    public function testGetCurrentSchema(): void
    {
        $connection = $this->getAdapter()->getDriver()->getConnection();
        self::assertIsString($connection->getCurrentSchema());
    }

    public function testGetResource(): void
    {
        $connection = $this->getAdapter()->getDriver()->getConnection();
        $connection->connect();

        self::assertInstanceOf(PDO::class, $connection->getResource());

        $connection->disconnect();
    }

    public function testConnect(): void
    {
        $connection = $this->getAdapter()->getDriver()->getConnection();
        self::assertSame($connection, $connection->connect());
        self::assertTrue($connection->isConnected());

        $connection->disconnect();
    }

    public function testIsConnected(): void
    {
        $connection = $this->getAdapter()->getDriver()->getConnection();
        self::assertFalse($connection->isConnected());
        self::assertSame($connection, $connection->connect());
        self::assertTrue($connection->isConnected());

        $connection->disconnect();
        //unset($connection);
    }

    public function testDisconnect(): void
    {
        $connection = $this->getAdapter()->getDriver()->getConnection();
        $connection->connect();
        self::assertTrue($connection->isConnected());
        $connection->disconnect();
        self::assertFalse($connection->isConnected());
    }

    /**
     * @todo   Implement testBeginTransaction().
     */
    public function testBeginTransaction(): never
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @todo   Implement testCommit().
     */
    public function testCommit(): never
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @todo   Implement testRollback().
     */
    public function testRollback(): never
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    public function testGetLastGeneratedValue(): never
    {
        $this->markTestIncomplete('Need to create a temporary sequence.');
        //$connection = new Connection($this->variables);
        //$connection->getLastGeneratedValue();
    }

    public function testConnectReturnsConnectionWhenResourceSet(): void
    {
        /** @var PDO $resource */
        $resource = $this->getAdapter()->getDriver()->getConnection()->getResource();
        /** @var PdoConnectionInterface&Connection $connection */
        $connection = $this->getAdapter()->getDriver()->getConnection();
        self::assertInstanceOf(PdoConnectionInterface::class, $connection);
        $connection->setResource($resource);
        self::assertSame($connection, $connection->connect());

        $connection->disconnect();
        unset($connection);
        unset($resource);
    }
}
