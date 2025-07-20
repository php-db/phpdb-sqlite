<?php

declare(strict_types=1);

namespace PhpDbTest\Adapter\Sqlite\Sql\Platform;

use PhpDb\Adapter\AdapterInterface;
use PhpDb\Adapter\Driver\DriverInterface;
use PhpDb\Adapter\Driver\PdoDriverInterface;
use PhpDb\Adapter\Driver\StatementInterface;
use PhpDb\Adapter\ParameterContainer;
use PhpDb\Adapter\Sqlite\Platform\Sqlite as SqlitePlatform;
use PhpDb\Adapter\Sqlite\Sql\Platform\SelectDecorator;
use PhpDb\Sql\Expression;
use PhpDb\Sql\Select;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(SelectDecorator::class)]
final class SelectDecoratorTest extends TestCase
{
    protected SqlitePlatform&MockObject $platform;
    protected PdoDriverInterface&MockObject $driver;

    protected function setUp(): void
    {

        $this->driver = $this->getMockBuilder(PdoDriverInterface::class)
                             ->getMock();

        $this->platform = $this->getMockBuilder(SqlitePlatform::class)
                            ->onlyMethods([])
                            ->setConstructorArgs([$this->driver])
                            ->getMock();

        parent::setUp();
    }

    public function testLimitAndOffset(): void
    {
        $select = new Select();
        $select->offset(5);
        $selectDecorator = new SelectDecorator();
        $selectDecorator->setSubject($select);
        $sqlString = $selectDecorator->getSqlString($this->platform);
        self::assertEquals('SELECT * LIMIT 18446744073709551615 OFFSET 5', $sqlString);
    }

    #[DataProvider('dataProviderUnionSyntaxFromCombine')]
    #[TestDox('integration test: Testing SelectDecorator will use Select an internal state to prepare a proper combine
statement')]
    public function testPrepareStatementPreparesUnionSyntaxFromCombine(
        Select $select,
        string $expectedSql,
        array $expectedParams
    ): void {
        $driver = $this->getMockBuilder(DriverInterface::class)->getMock();
        $driver->expects($this->any())->method('formatParameterName')->willReturn('?');

        // test
        $adapter = $this->getMockBuilder(AdapterInterface::class)
                        ->onlyMethods([])
                        ->setConstructorArgs([
                            $driver,
                            $this->platform,
                        ])
                        ->getMock();

        $parameterContainer = new ParameterContainer();
        $statement          = $this->getMockBuilder(StatementInterface::class)->getMock();
        $statement->expects($this->any())->method('getParameterContainer')
                  ->willReturn($parameterContainer);

        $statement->expects($this->once())->method('setSql')->with($expectedSql);

        $selectDecorator = new SelectDecorator();
        $selectDecorator->setSubject($select);
        $selectDecorator->prepareStatement($adapter, $statement);

        self::assertEquals($expectedParams, $parameterContainer->getNamedArray());
    }

    #[DataProvider('dataProviderUnionSyntaxFromCombine')]
    #[TestDox('integration test: Testing SelectDecorator will use Select an internal state to prepare a proper combine
statement')]
    public function testGetSqlStringPreparesUnionSyntaxFromCombine(
        Select $select,
        string $expectedSql
    ): void {
        $parameterContainer = new ParameterContainer();
        $statement          = $this->getMockBuilder(StatementInterface::class)->getMock();
        $statement->expects($this->any())->method('getParameterContainer')
                  ->willReturn($parameterContainer);

        $selectDecorator = new SelectDecorator();
        $selectDecorator->setSubject($select);
        self::assertEquals($expectedSql, $selectDecorator->getSqlString($this->platform));
    }

    /**
     * Create a data provider for union syntax that would come from combine
     *
     * @psalm-return array<array-key, array{
     *     0: Select,
     *     1: string,
     *     2: array<string, mixed>,
     *     3: string
     * }>
     */
    public static function dataProviderUnionSyntaxFromCombine(): array
    {
        $select0 = new Select();
        $select0->from('foo');
        $select1 = clone $select0;
        $select0->combine($select1);

        $expectedPrepareSql0 = '( SELECT "foo".* FROM "foo" ) UNION ( SELECT "foo".* FROM "foo" )';
        $expectedParams0     = [];
        $expectedSql0        = '( SELECT "foo".* FROM "foo" ) UNION ( SELECT "foo".* FROM "foo" )';

        // nested single limit & offset in field param
        $nestedSelect0 = new Select();
        $nestedSelect0->from('foo1')
                      ->columns([
                          'cnt' => new Expression('count(foo1.id)'),
                      ])->limit(100)->offset(500);

        $select3 = new Select();
        $select3->from('foo')
                ->columns([
                    'res' => $nestedSelect0,
                ])
                ->limit(10)->offset(50);

        $expectedPrepareSql3 =
            'SELECT (SELECT count(foo1.id) AS "cnt" FROM "foo1" LIMIT ? OFFSET ?) AS "res"'
            . ' FROM "foo" LIMIT ? OFFSET ?';
        $expectedParams3     = [
            'subselect1limit'  => 100,
            'subselect1offset' => 500,
            'limit'            => 10,
            'offset'           => 50,
        ];
        $expectedSql3        = 'SELECT (SELECT count(foo1.id) AS "cnt"'
            . ' FROM "foo1" LIMIT 100 OFFSET 500) AS "res"'
            . ' FROM "foo" LIMIT 10 OFFSET 50';
        // multiple nested query
        $nestedSelect0 = new Select();
        $nestedSelect0->from('foo1')
                      ->columns([
                          'cnt' => new Expression('count(foo1.id)'),
                      ])->limit(100)->offset(500);

        $nestedSelect1 = new Select();
        $nestedSelect1->from('foo2')
                      ->columns([
                          'cnt' => new Expression('count(foo2.id)'),
                      ])->limit(50)->offset(101);

        $select4 = new Select();
        $select4->from('foo')
                ->columns([
                    'res'  => $nestedSelect0,
                    'res0' => $nestedSelect1,
                ])
                ->limit(10)->offset(5);

        $expectedPrepareSql4 =
            'SELECT (SELECT count(foo1.id) AS "cnt" FROM "foo1" LIMIT ? OFFSET ?) AS "res",'
            . ' (SELECT count(foo2.id) AS "cnt" FROM "foo2" LIMIT ? OFFSET ?) AS "res0"'
            . ' FROM "foo" LIMIT ? OFFSET ?';
        $expectedParams4     = [
            'subselect1limit'  => 100,
            'subselect1offset' => 500,
            'subselect2limit'  => 50,
            'subselect2offset' => 101,
            'limit'            => 10,
            'offset'           => 5,
        ];
        $expectedSql4        = 'SELECT (SELECT count(foo1.id) AS "cnt" FROM "foo1" LIMIT 100 OFFSET 500) AS "res",'
            . ' (SELECT count(foo2.id) AS "cnt" FROM "foo2" LIMIT 50 OFFSET 101) AS "res0"'
            . ' FROM "foo" LIMIT 10 OFFSET 5';

        return [
            [$select0, $expectedPrepareSql0, $expectedParams0, $expectedSql0],
            [$select3, $expectedPrepareSql3, $expectedParams3, $expectedSql3],
            [$select4, $expectedPrepareSql4, $expectedParams4, $expectedSql4],
        ];
    }
}
