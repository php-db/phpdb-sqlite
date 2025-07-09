<?php

namespace PhpDbIntegrationTest\Adapter\Sqlite\Platform;

use PhpDb\Adapter\Sqlite\Driver\Pdo\Connection;
use PhpDb\Adapter\Sqlite\Driver\Pdo\Driver;
use PhpDb\Adapter\Sqlite\Platform\Sqlite;
use Override;
use PDO;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

use function extension_loaded;

#[Group('integration')]
#[Group('integration-sqlite')]
#[CoversMethod(Sqlite::class, 'quoteValue')]
final class SqliteTest extends TestCase
{
    /** @var array<string, resource|PDO> */
    public array|PDO $adapters = [];

    /**
     * @return void
     */
    public function testQuoteValueWithPdoSqlite()
    {
        if (! $this->adapters['pdo_sqlite'] instanceof PDO) {
            $this->markTestSkipped('SQLite (PDO_SQLITE) not configured in unit test configuration file');
        }

        $sqlite = new Sqlite($this->adapters['pdo_sqlite']);
        $value  = $sqlite->quoteValue('value');
        self::assertEquals('\'value\'', $value);

        $sqlite = new Sqlite(new Driver(new Connection($this->adapters['pdo_sqlite'])));
        $value  = $sqlite->quoteValue('value');
        self::assertEquals('\'value\'', $value);
    }

    #[Override]
    protected function setUp(): void
    {
        if (! extension_loaded('pdo')) {
            $this->markTestSkipped(self::class . ' integration tests are not enabled!');
        }

        if (extension_loaded('pdo')) {
            $this->adapters['pdo_sqlite'] = new PDO(
                'sqlite::memory'
            );
        }
    }
}
