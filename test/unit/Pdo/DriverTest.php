<?php

declare(strict_types=1);

namespace PhpDbTest\Sqlite\Pdo;

use Override;
use PDO;
use PDOStatement;
use PhpDb\Adapter\Driver\Pdo\Result;
use PhpDb\Adapter\Driver\PdoDriverAwareInterface;
use PhpDb\Adapter\Driver\PdoDriverInterface;
use PhpDb\Adapter\Driver\ResultInterface;
use PhpDb\Adapter\Driver\StatementInterface;
use PhpDb\Exception\RuntimeException;
use PhpDb\Sqlite\Pdo\Connection;
use PhpDb\Sqlite\Pdo\Driver;
use PhpDb\Sqlite\Pdo\Feature\SqliteRowCounter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(Driver::class)]
#[Group('unit')]
final class DriverTest extends TestCase
{
    protected Driver $pdo;

    /** @psalm-return array<array-key, array{0: string}> */
    public static function getInvalidParamName(): array
    {
        return [
            ['foo%'],
            ['foo-'],
            ['foo$'],
            ['foo0!'],
        ];
    }

    /** @psalm-return array<array-key, array{0: int|string, 1: null|string, 2: string}> */
    public static function getParamsAndType(): array
    {
        return [
            ['foo',     null,                                       ':foo'],
            ['foo_bar', null,                                       ':foo_bar'],
            ['123foo',  null,                                       ':123foo'],
            [1,         null,                                       '?'],
            ['1',       null,                                       '?'],
            ['foo',     PdoDriverInterface::PARAMETERIZATION_NAMED, ':foo'],
            ['foo_bar', PdoDriverInterface::PARAMETERIZATION_NAMED, ':foo_bar'],
            ['123foo',  PdoDriverInterface::PARAMETERIZATION_NAMED, ':123foo'],
            [1,         PdoDriverInterface::PARAMETERIZATION_NAMED, ':1'],
            ['1',       PdoDriverInterface::PARAMETERIZATION_NAMED, ':1'],
            [':foo',    null,                                       ':foo'],
        ];
    }

    #[Test]
    public function createResultCarriesTheConnectionsLastGeneratedValue(): void
    {
        $result = $this->createDriverWithRowCounter()->createResult($this->createStub(PDOStatement::class));

        static::assertSame('42', $result->getGeneratedValue());
    }

    #[Test]
    public function createResultCountsRowsWithTheRowCounterWhenTheStatementReturnsColumns(): void
    {
        $countStatement = $this->createStub(PDOStatement::class);
        $countStatement->method('fetch')->willReturn(['count' => '5']);
        $pdo = $this->createStub(PDO::class);
        $pdo->method('query')->willReturn($countStatement);

        $resource = $this->createStub(PDOStatement::class);
        $resource->method('columnCount')->willReturn(1);

        $result = $this->createDriverWithRowCounter($pdo)->createResult($resource, 'SELECT * FROM foo');

        static::assertSame(5, $result->count());
    }

    #[Test]
    public function createResultSkipsTheRowCounterWhenTheStatementReturnsNoColumns(): void
    {
        $resource = $this->createStub(PDOStatement::class);
        $resource->method('columnCount')->willReturn(0);
        $resource->method('rowCount')->willReturn(3);

        $result = $this->createDriverWithRowCounter()->createResult($resource, 'DELETE FROM foo');

        static::assertSame(0, $result->count());
    }

    #[DataProvider('getParamsAndType')]
    public function testFormatParameterName(int|string $name, ?string $type, string $expected): void
    {
        $result = $this->pdo->formatParameterName((string) $name, $type);
        $this->assertEquals($expected, $result);
    }

    #[DataProvider('getInvalidParamName')]
    public function testFormatParameterNameWithInvalidCharacters(string $name): void
    {
        $this->expectException(RuntimeException::class);
        $this->pdo->formatParameterName($name);
    }

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    #[Override]
    protected function setUp(): void
    {
        $connection = new Connection(['dsn' => ':memory:']);

        /** @var StatementInterface&PdoDriverAwareInterface&MockObject $statementPrototype */
        $statementPrototype = $this->createMockForIntersectionOfInterfaces([
            StatementInterface::class,
            PdoDriverAwareInterface::class,
        ]);

        $this->pdo = new Driver(
            $connection,
            $statementPrototype,
            $this->createMock(ResultInterface::class),
        );
    }

    private function createDriverWithRowCounter(?PDO $pdo = null): Driver
    {
        $connection = $this->createStub(Connection::class);
        $connection->method('getLastGeneratedValue')->willReturn('42');
        $connection->method('getResource')->willReturn($pdo ?? $this->createStub(PDO::class));

        /** @var StatementInterface&PdoDriverAwareInterface&MockObject $statementPrototype */
        $statementPrototype = $this->createMockForIntersectionOfInterfaces([
            StatementInterface::class,
            PdoDriverAwareInterface::class,
        ]);

        return new Driver($connection, $statementPrototype, new Result(), [new SqliteRowCounter()]);
    }
}
