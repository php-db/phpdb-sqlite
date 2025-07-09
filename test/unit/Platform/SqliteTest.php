<?php

namespace PhpDbTest\Adapter\Sqlite\Sqlite\Platform;

use PhpDb\Adapter\Sqlite\Driver\Pdo\Connection;
use PhpDb\Adapter\Sqlite\Driver\Pdo\Driver;
use PhpDb\Adapter\Sqlite\Platform\Sqlite;
use Override;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

use function file_exists;
use function realpath;
use function restore_error_handler;
use function set_error_handler;
use function touch;
use function unlink;

use const E_USER_NOTICE;

#[CoversMethod(Sqlite::class, 'getName')]
#[CoversMethod(Sqlite::class, 'getQuoteIdentifierSymbol')]
#[CoversMethod(Sqlite::class, 'quoteIdentifier')]
#[CoversMethod(Sqlite::class, 'quoteIdentifierChain')]
#[CoversMethod(Sqlite::class, 'getQuoteValueSymbol')]
#[CoversMethod(Sqlite::class, 'quoteValue')]
#[CoversMethod(Sqlite::class, 'quoteTrustedValue')]
#[CoversMethod(Sqlite::class, 'quoteValueList')]
#[CoversMethod(Sqlite::class, 'getIdentifierSeparator')]
#[CoversMethod(Sqlite::class, 'quoteIdentifierInFragment')]
final class SqliteTest extends TestCase
{
    protected Sqlite $platform;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    #[Override]
    protected function setUp(): void
    {
        $this->platform = new Sqlite();
    }

    public function testGetName(): void
    {
        self::assertEquals('SQLite', $this->platform->getName());
    }

    public function testGetQuoteIdentifierSymbol(): void
    {
        self::assertEquals('"', $this->platform->getQuoteIdentifierSymbol());
    }

    public function testQuoteIdentifier(): void
    {
        self::assertEquals('"identifier"', $this->platform->quoteIdentifier('identifier'));
    }

    public function testQuoteIdentifierChain(): void
    {
        self::assertEquals('"identifier"', $this->platform->quoteIdentifierChain('identifier'));
        self::assertEquals('"identifier"', $this->platform->quoteIdentifierChain(['identifier']));
        self::assertEquals('"schema"."identifier"', $this->platform->quoteIdentifierChain(['schema', 'identifier']));
    }

    public function testGetQuoteValueSymbol(): void
    {
        self::assertEquals("'", $this->platform->getQuoteValueSymbol());
    }

    public function testQuoteValueRaisesNoticeWithoutPlatformSupport(): void
    {
        $raisedNotice = false;
        set_error_handler(function ($errno, $errstr) use (&$raisedNotice) {
            $this->assertEquals(E_USER_NOTICE, $errno);
            $this->assertEquals(
                $errstr,
                'Attempting to quote a value in PhpDb\Adapter\Sqlite\Platform\Sqlite without extension/driver support can '
                    . 'introduce security vulnerabilities in a production environment'
            );
            $raisedNotice = true;
        });

        $this->platform->quoteValue('value');
        self::assertTrue($raisedNotice);

        restore_error_handler();
    }

    public function testQuoteValue(): void
    {
        self::assertEquals("'value'", @$this->platform->quoteValue('value'));
        self::assertEquals("'Foo O\\'Bar'", @$this->platform->quoteValue("Foo O'Bar"));
        self::assertEquals(
            '\'\\\'; DELETE FROM some_table; -- \'',
            @$this->platform->quoteValue('\'; DELETE FROM some_table; -- ')
        );
        self::assertEquals(
            "'\\\\\\'; DELETE FROM some_table; -- '",
            @$this->platform->quoteValue('\\\'; DELETE FROM some_table; -- ')
        );
    }

    public function testQuoteTrustedValue(): void
    {
        self::assertEquals("'value'", $this->platform->quoteTrustedValue('value'));
        self::assertEquals("'Foo O\\'Bar'", $this->platform->quoteTrustedValue("Foo O'Bar"));
        self::assertEquals(
            '\'\\\'; DELETE FROM some_table; -- \'',
            $this->platform->quoteTrustedValue('\'; DELETE FROM some_table; -- ')
        );

        //                   '\\\'; DELETE FROM some_table; -- '  <- actual below
        self::assertEquals(
            "'\\\\\\'; DELETE FROM some_table; -- '",
            $this->platform->quoteTrustedValue('\\\'; DELETE FROM some_table; -- ')
        );
    }

    public function testQuoteValueList(): void
    {
        $raisedNotice = false;
        set_error_handler(function ($errno, $errstr) use (&$raisedNotice) {
            $this->assertEquals(E_USER_NOTICE, $errno);
            $this->assertEquals(
                $errstr,
                'Attempting to quote a value in PhpDb\Adapter\Sqlite\Platform\Sqlite without extension/driver support can '
                    . 'introduce security vulnerabilities in a production environment'
            );
            $raisedNotice = true;
        });

        self::assertEquals("'Foo O\\'Bar'", $this->platform->quoteValueList("Foo O'Bar"));
        self::assertTrue($raisedNotice);

        restore_error_handler();
    }

    public function testGetIdentifierSeparator(): void
    {
        self::assertEquals('.', $this->platform->getIdentifierSeparator());
    }

    public function testQuoteIdentifierInFragment(): void
    {
        self::assertEquals('"foo"."bar"', $this->platform->quoteIdentifierInFragment('foo.bar'));
        self::assertEquals('"foo" as "bar"', $this->platform->quoteIdentifierInFragment('foo as bar'));

        // single char words
        self::assertEquals(
            '("foo"."bar" = "boo"."baz")',
            $this->platform->quoteIdentifierInFragment('(foo.bar = boo.baz)', ['(', ')', '='])
        );

        // case insensitive safe words
        self::assertEquals(
            '("foo"."bar" = "boo"."baz") AND ("foo"."baz" = "boo"."baz")',
            $this->platform->quoteIdentifierInFragment(
                '(foo.bar = boo.baz) AND (foo.baz = boo.baz)',
                ['(', ')', '=', 'and']
            )
        );

        // case insensitive safe words in field
        self::assertEquals(
            '("foo"."bar" = "boo".baz) AND ("foo".baz = "boo".baz)',
            $this->platform->quoteIdentifierInFragment(
                '(foo.bar = boo.baz) AND (foo.baz = boo.baz)',
                ['(', ')', '=', 'and', 'bAz']
            )
        );
    }

    public function testCanCloseConnectionAfterQuoteValue(): void
    {
        // Creating the SQLite database file
        $filePath = __DIR__ . "/_files/sqlite.db";
        if (! file_exists($filePath)) {
            touch($filePath);
        }

        $driver = new Driver(new Connection([
            'driver'   => 'Pdo_Sqlite',
            'database' => ':memory',
        ]));

        $this->platform->setDriver($driver);
        $this->platform->quoteValue("some; random]/ value");
        $this->platform->quoteTrustedValue("some; random]/ value");

        // Closing the connection so we can delete the file
        $driver->getConnection()->disconnect();

        @unlink($filePath);

        self::assertFileDoesNotExist($filePath);
    }
}
