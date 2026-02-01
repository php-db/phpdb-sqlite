<?php

declare(strict_types=1);

namespace PhpDbTest\Sqlite\Pdo;

use Override;
use PhpDb\Adapter\Driver\PdoDriverAwareInterface;
use PhpDb\Adapter\Driver\PdoDriverInterface;
use PhpDb\Adapter\Driver\ResultInterface;
use PhpDb\Adapter\Driver\StatementInterface;
use PhpDb\Exception\RuntimeException;
use PhpDb\Sqlite\Pdo\Connection;
use PhpDb\Sqlite\Pdo\Driver;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversMethod(Driver::class, 'getDatabasePlatformName')]
final class DriverTest extends TestCase
{
    protected Driver $pdo;

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

    public function testGetDatabasePlatformName(): void
    {
        $this->pdo->getConnection()->setConnectionParameters(['pdodriver' => 'pdo_sqlite']);
        self::assertEquals('Sqlite', $this->pdo->getDatabasePlatformName());
        self::assertEquals('SQLite', $this->pdo->getDatabasePlatformName(PdoDriverInterface::NAME_FORMAT_NATURAL));
    }

    /** @psalm-return array<array-key, array{0: int|string, 1: null|string, 2: string}> */
    public static function getParamsAndType(): array
    {
        return [
            ['foo', null, ':foo'],
            ['foo_bar', null, ':foo_bar'],
            ['123foo', null, ':123foo'],
            [1, null, '?'],
            ['1', null, '?'],
            ['foo', PdoDriverInterface::PARAMETERIZATION_NAMED, ':foo'],
            ['foo_bar', PdoDriverInterface::PARAMETERIZATION_NAMED, ':foo_bar'],
            ['123foo', PdoDriverInterface::PARAMETERIZATION_NAMED, ':123foo'],
            [1, PdoDriverInterface::PARAMETERIZATION_NAMED, ':1'],
            ['1', PdoDriverInterface::PARAMETERIZATION_NAMED, ':1'],
            [':foo', null, ':foo'],
        ];
    }

    #[DataProvider('getParamsAndType')]
    public function testFormatParameterName(int|string $name, ?string $type, string $expected): void
    {
        $result = $this->pdo->formatParameterName((string) $name, $type);
        $this->assertEquals($expected, $result);
    }

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

    #[DataProvider('getInvalidParamName')]
    public function testFormatParameterNameWithInvalidCharacters(string $name): void
    {
        $this->expectException(RuntimeException::class);
        $this->pdo->formatParameterName($name);
    }
}
