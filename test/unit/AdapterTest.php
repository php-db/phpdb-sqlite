<?php

declare(strict_types=1);

namespace PhpDbTest\Adapter\Sqlite;

use Override;
use PhpDb\Adapter\Adapter;
use PhpDb\Adapter\Driver\DriverInterface;
use PhpDb\Adapter\Driver\Pdo\AbstractPdoConnection;
use PhpDb\Adapter\Driver\Pdo\Statement;
use PhpDb\Adapter\Driver\PdoDriverInterface;
use PhpDb\Adapter\Driver\ResultInterface;
use PhpDb\Adapter\Driver\StatementInterface;
use PhpDb\Adapter\Profiler;
use PhpDb\Adapter\Sqlite\Driver\Pdo\Pdo;
use PhpDb\Adapter\Sqlite\Platform\Sqlite as SqlitePlatform;
use PhpDb\ResultSet\ResultSetInterface;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversMethod(Adapter::class, 'setProfiler')]
#[CoversMethod(Adapter::class, 'getProfiler')]
#[CoversMethod(Adapter::class, 'createDriver')]
#[CoversMethod(Adapter::class, 'createPlatform')]
#[CoversMethod(Adapter::class, 'getDriver')]
#[CoversMethod(Adapter::class, 'getPlatform')]
#[CoversMethod(Adapter::class, 'getQueryResultSetPrototype')]
#[CoversMethod(Adapter::class, 'getCurrentSchema')]
#[CoversMethod(Adapter::class, 'query')]
#[CoversMethod(Adapter::class, 'createStatement')]
#[CoversMethod(Adapter::class, '__get')]
final class AdapterTest extends TestCase
{
    protected DriverInterface&MockObject $mockDriver;

    protected SqlitePlatform $mockPlatform;

    protected AbstractPdoConnection&MockObject $mockConnection;

    protected StatementInterface&MockObject $mockStatement;

    protected ResultSetInterface&MockObject $mockResultSet;

    protected Adapter $adapter;

    /**
     * @throws \Exception
     */
    #[Override]
    protected function setUp(): void
    {
        $this->mockConnection = $this->createMock(AbstractPdoConnection::class);
        $this->mockPlatform   = new SqlitePlatform($this->createMock(PdoDriverInterface::class));
        $this->mockStatement  = $this->getMockBuilder(Statement::class)->getMock();
        $this->mockResultSet  = $this->getMockBuilder(ResultSetInterface::class)->getMock();
        $resultInterface      = $this->getMockBuilder(ResultInterface::class)->getMock();
        $this->mockDriver     = $this->getMockBuilder(Pdo::class)
            ->setConstructorArgs([
                $this->mockConnection,
                $this->mockStatement,
                $resultInterface,
            ])
            ->getMock();

        $this->mockDriver->method('getDatabasePlatformName')->willReturn('Sqlite');
        $this->mockDriver->method('checkEnvironment')->willReturn(true);
        $this->mockDriver->method('getConnection')->willReturn($this->mockConnection);
        $this->mockDriver->method('createStatement')->willReturn($this->mockStatement);

        $this->adapter = new Adapter(
            $this->mockDriver,
            $this->mockPlatform,
            $this->mockResultSet
        );
    }

    #[TestDox('unit test: Test setProfiler() will store profiler')]
    public function testSetProfiler(): void
    {
        $ret = $this->adapter->setProfiler(new Profiler\Profiler());
        self::assertSame($this->adapter, $ret);
    }

    #[TestDox('unit test: Test getDriver() will return driver object')]
    public function testGetDriver(): void
    {
        self::assertSame($this->mockDriver, $this->adapter->getDriver());
    }

    #[TestDox('unit test: Test getPlatform() returns platform object')]
    public function testGetPlatform(): void
    {
        self::assertSame($this->mockPlatform, $this->adapter->getPlatform());
    }

    #[TestDox('unit test: Test getCurrentSchema() returns current schema from connection object')]
    public function testGetCurrentSchema(): void
    {
        $this->mockConnection->expects($this->any())->method('getCurrentSchema')->willReturn('FooSchema');
        self::assertEquals('FooSchema', $this->adapter->getCurrentSchema());
    }

    /**
     * @throws \Exception
     */
    #[TestDox('unit test: Test query() in prepare mode produces a statement object')]
    public function testQueryWhenPreparedProducesStatement(): void
    {
        $s = $this->adapter->query('SELECT foo');
        self::assertSame($this->mockStatement, $s);
    }

    /**
     * @throws \Exception
     */
    #[TestDox('unit test: Test query() in prepare mode, with array of parameters, produces a result object')]
    public function testQueryWhenPreparedWithParameterArrayProducesResult(): void
    {
        $parray    = ['bar' => 'foo'];
        $sql       = 'SELECT foo, :bar';
        $statement = $this->getMockBuilder(StatementInterface::class)->getMock();
        $result    = $this->getMockBuilder(ResultInterface::class)->getMock();
        $this->mockDriver->expects($this->any())->method('createStatement')
            ->with($sql)->willReturn($statement);
        $this->mockStatement->expects($this->any())->method('execute')->willReturn($result);

        $r = $this->adapter->query($sql, $parray);
        self::assertSame($result, $r);
    }

    #[TestDox('unit test: Test createStatement() produces a statement object')]
    public function testCreateStatement(): void
    {
        self::assertSame($this->mockStatement, $this->adapter->createStatement());
    }
}
