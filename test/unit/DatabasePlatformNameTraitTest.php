<?php

declare(strict_types=1);

namespace PhpDbTest\Sqlite;

use PhpDb\Adapter\Driver\DriverInterface;
use PhpDb\Adapter\Exception\InvalidArgumentException;
use PhpDb\Sqlite\DatabasePlatformNameTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(DatabasePlatformNameTrait::class)]
final class DatabasePlatformNameTraitTest extends TestCase
{
    private object $traitObject;

    protected function setUp(): void
    {
        $this->traitObject = new class {
            use DatabasePlatformNameTrait;
        };
    }

    public function testGetDatabasePlatformNameWithCamelCaseFormat(): void
    {
        $result = $this->traitObject->getDatabasePlatformName(DriverInterface::NAME_FORMAT_CAMELCASE);

        self::assertSame('Sqlite', $result);
    }

    public function testGetDatabasePlatformNameWithNaturalFormat(): void
    {
        $result = $this->traitObject->getDatabasePlatformName(DriverInterface::NAME_FORMAT_NATURAL);

        self::assertSame('SQLite', $result);
    }

    public function testGetDatabasePlatformNameWithInvalidFormatThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid name format provided');

        $this->traitObject->getDatabasePlatformName('INVALID_FORMAT');
    }

    public function testGetDatabasePlatformNameDefaultsToCamelCase(): void
    {
        $result = $this->traitObject->getDatabasePlatformName();

        self::assertSame('Sqlite', $result);
    }
}
