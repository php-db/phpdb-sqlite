<?php

declare(strict_types=1);

namespace PhpDbTest\Adapter\Sqlite\Platform;

use PDO;
use PhpDb\Adapter\Driver\PdoDriverInterface;
use PhpDb\Adapter\Sqlite\Platform\Sqlite;
use PhpDb\Adapter\Sqlite\Sql\Platform\Sqlite as SqlPlatformDecorator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

#[CoversClass(Sqlite::class)]
final class SqliteTest extends TestCase
{
    private Sqlite $platform;

    protected function setUp(): void
    {
        $pdoMock       = $this->createMock(PDO::class);
        $this->platform = new Sqlite($pdoMock);
    }

    public function testGetNameReturnsSqlite(): void
    {
        $pdoMock  = $this->createMock(PDO::class);
        $platform = new Sqlite($pdoMock);

        self::assertSame('SQLite', $platform->getName());
    }

    public function testPlatformNameConstant(): void
    {
        self::assertSame('SQLite', Sqlite::PLATFORM_NAME);
    }

    public function testConstructWithPdo(): void
    {
        $pdoMock  = $this->createMock(PDO::class);
        $platform = new Sqlite($pdoMock);

        self::assertInstanceOf(Sqlite::class, $platform);
    }

    public function testConstructWithPdoDriver(): void
    {
        $driverMock = $this->createMock(PdoDriverInterface::class);
        $platform   = new Sqlite($driverMock);

        self::assertInstanceOf(Sqlite::class, $platform);
    }

    public function testGetSqlPlatformDecorator(): void
    {
        $pdoMock  = $this->createMock(PDO::class);
        $platform = new Sqlite($pdoMock);

        $decorator = $platform->getSqlPlatformDecorator();

        self::assertInstanceOf(SqlPlatformDecorator::class, $decorator);
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

        set_error_handler(function (int $errno, string $errstr) use (&$raisedNotice) {
            $this->assertEquals(E_USER_NOTICE, $errno);
            $this->assertEquals(
                $errstr,
                // phpcs:ignore Generic.Files.LineLength
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

        /**
         * @psalm-suppress InvalidArgument
         */
        set_error_handler(function ($errno, $errstr) use (&$raisedNotice) {
            $this->assertEquals(E_USER_NOTICE, $errno);
            $this->assertEquals(
                $errstr,
                // phpcs:ignore Generic.Files.LineLength
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
}
