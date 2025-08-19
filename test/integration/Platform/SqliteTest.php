<?php

declare(strict_types=1);

namespace PhpDbIntegrationTest\Adapter\Sqlite\Platform;

use PhpDb\Adapter\Sqlite\Platform\Sqlite;
use PhpDbIntegrationTest\Adapter\Sqlite\Container\TestAsset\SetupTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('integration')]
#[Group('integration-sqlite')]
#[CoversClass(Sqlite::class)]
#[CoversMethod(Sqlite::class, 'quoteValue')]
final class SqliteTest extends TestCase
{
    use SetupTrait;

    public function testQuoteValueWithPdoSqlite(): void
    {
        $sqlite = $this->getAdapter()->getPlatform();
        $value  = $sqlite->quoteValue('value');
        self::assertEquals('\'value\'', $value);
    }
}
