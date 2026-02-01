<?php

declare(strict_types=1);

namespace PhpDbTest\Sqlite;

use PDO;
use PhpDb\Adapter\Driver\PdoDriverInterface;
use PhpDb\Adapter\Exception\VunerablePlatformQuoteException;
use PhpDb\Sqlite\AdapterPlatform;
use PhpDb\Sqlite\Sql\Platform as SqlPlatformDecorator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AdapterPlatform::class)]
final class AdapterPlatformTest extends TestCase
{
    private AdapterPlatform $platform;

    protected function setUp(): void
    {
        $pdoMock        = $this->createMock(PDO::class);
        $this->platform = new AdapterPlatform($pdoMock);
    }

    public function testGetNameReturnsSqlite(): void
    {
        $pdoMock  = $this->createMock(PDO::class);
        $platform = new AdapterPlatform($pdoMock);

        self::assertSame('SQLite', $platform->getName());
    }

    public function testPlatformNameConstant(): void
    {
        self::assertSame('SQLite', AdapterPlatform::PLATFORM_NAME);
    }

    public function testConstructWithPdo(): void
    {
        $pdoMock  = $this->createMock(PDO::class);
        $platform = new AdapterPlatform($pdoMock);

        self::assertInstanceOf(AdapterPlatform::class, $platform);
    }

    public function testConstructWithPdoDriver(): void
    {
        $driverMock = $this->createMock(PdoDriverInterface::class);
        $platform   = new AdapterPlatform($driverMock);

        self::assertInstanceOf(AdapterPlatform::class, $platform);
    }

    public function testGetSqlPlatformDecorator(): void
    {
        $pdoMock  = $this->createMock(PDO::class);
        $platform = new AdapterPlatform($pdoMock);

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

    public function testQuoteValueThrowsExeceptionWithoutDriverSupport(): void
    {
        $platform = new AdapterPlatform();
        //$this->expectNotToPerformAssertions();
        $this->expectException(VunerablePlatformQuoteException::class);
        $platform->quoteValue('value');
    }

    public function testQuoteValueList(): void
    {
        $expected = "'Foo O\\'Bar'";
        $platform = new AdapterPlatform();
        $this->expectException(VunerablePlatformQuoteException::class);
        $actual = $platform->quoteValueList("Foo O'Bar");
        self::assertEquals($expected, $actual);
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
