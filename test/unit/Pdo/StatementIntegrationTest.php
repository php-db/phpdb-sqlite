<?php

declare(strict_types=1);

namespace PhpDbTest\Adapter\Sqlite\Driver\Pdo;

use Override;
use PDO;
use PDOStatement;
use PhpDb\Adapter\Driver\Pdo\Statement;
use PhpDb\Adapter\Sqlite\Pdo\Driver;
use PhpDbTest\Adapter\Sqlite\Driver\Pdo\TestAsset\CtorlessPdo;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversMethod(Statement::class, 'execute')]
final class StatementIntegrationTest extends TestCase
{
    protected Statement $statement;

    protected PDOStatement&MockObject $pdoStatementMock;

    public function testStatementExecuteWillConvertPhpBoolToPdoBoolWhenBinding(): void
    {
        $this->pdoStatementMock->expects($this->any())->method('bindParam')->with(
            $this->equalTo(':foo'),
            $this->equalTo(false),
            $this->equalTo(PDO::PARAM_BOOL)
        );
        $this->statement->execute(['foo' => false]);
    }

    public function testStatementExecuteWillUsePdoStrByDefaultWhenBinding(): void
    {
        $this->pdoStatementMock->expects($this->any())->method('bindParam')->with(
            $this->equalTo(':foo'),
            $this->equalTo('bar'),
            $this->equalTo(PDO::PARAM_STR)
        );
        $this->statement->execute(['foo' => 'bar']);
    }

    public function testStatementExecuteWillUsePdoStrForStringIntegerWhenBinding(): void
    {
        $this->pdoStatementMock->expects($this->any())->method('bindParam')->with(
            $this->equalTo(':foo'),
            $this->equalTo('123'),
            $this->equalTo(PDO::PARAM_STR)
        );
        $this->statement->execute(['foo' => '123']);
    }

    public function testStatementExecuteWillUsePdoIntForIntWhenBinding(): void
    {
        $this->pdoStatementMock->expects($this->any())->method('bindParam')->with(
            $this->equalTo(':foo'),
            $this->equalTo(123),
            $this->equalTo(PDO::PARAM_INT)
        );
        $this->statement->execute(['foo' => 123]);
    }

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    #[Override]
    protected function setUp(): void
    {
        $driver = $this->getMockBuilder(Driver::class)
                       ->onlyMethods(['createResult'])
                       ->disableOriginalConstructor()
                       ->getMock();

        $this->statement = new Statement();
        $this->statement->setDriver($driver);
        $this->statement->initialize(new CtorlessPdo(
            $this->pdoStatementMock = $this->getMockBuilder(PDOStatement::class)
                                           ->onlyMethods(['execute', 'bindParam'])
                                           ->getMock()
        ));
    }
}
