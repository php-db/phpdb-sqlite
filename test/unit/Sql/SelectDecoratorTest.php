<?php

declare(strict_types=1);

namespace PhpDbTest\Sqlite\Sql\Platform;

use PhpDb\Adapter\Driver\DriverInterface;
use PhpDb\Adapter\ParameterContainer;
use PhpDb\Adapter\Platform\PlatformInterface;
use PhpDb\Sql\Select;
use PhpDb\Sqlite\Sql\SelectDecorator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

#[CoversClass(SelectDecorator::class)]
final class SelectDecoratorTest extends TestCase
{
    private SelectDecorator $decorator;

    protected function setUp(): void
    {
        $this->decorator = new SelectDecorator();
    }

    public function testSetSubject(): void
    {
        $select = new Select();
        $result = $this->decorator->setSubject($select);

        self::assertSame($this->decorator, $result);

        $reflection      = new ReflectionClass($this->decorator);
        $subjectProperty = $reflection->getProperty('subject');
        $subject         = $subjectProperty->getValue($this->decorator);

        self::assertSame($select, $subject);
    }

    public function testProcessLimitWithoutLimitAndWithoutOffset(): void
    {
        $platformMock = $this->createMock(PlatformInterface::class);

        $reflection = new ReflectionClass($this->decorator);
        $method     = $reflection->getMethod('processLimit');

        $result = $method->invoke($this->decorator, $platformMock);

        self::assertNull($result);
    }

    public function testProcessLimitWithLimit(): void
    {
        $this->decorator->limit(10);

        $platformMock = $this->createMock(PlatformInterface::class);

        $reflection = new ReflectionClass($this->decorator);
        $method     = $reflection->getMethod('processLimit');

        $result = $method->invoke($this->decorator, $platformMock);

        self::assertSame([10], $result);
    }

    public function testProcessLimitWithLimitAndParameterContainer(): void
    {
        $this->decorator->limit(15);

        $platformMock = $this->createMock(PlatformInterface::class);
        $driverMock   = $this->createMock(DriverInterface::class);
        $driverMock->expects(self::once())
            ->method('formatParameterName')
            ->with('limit')
            ->willReturn(':limit');

        $parameterContainer = new ParameterContainer();

        $reflection          = new ReflectionClass($this->decorator);
        $processInfoProperty = $reflection->getProperty('processInfo');
        $processInfoProperty->setValue($this->decorator, ['paramPrefix' => '']);

        $method = $reflection->getMethod('processLimit');
        $result = $method->invoke($this->decorator, $platformMock, $driverMock, $parameterContainer);

        self::assertSame([':limit'], $result);
        self::assertTrue($parameterContainer->offsetExists('limit'));
        self::assertSame(15, $parameterContainer->offsetGet('limit'));
    }

    public function testProcessLimitWithoutLimitButWithOffset(): void
    {
        $this->decorator->offset(5);

        $platformMock = $this->createMock(PlatformInterface::class);

        $reflection = new ReflectionClass($this->decorator);
        $method     = $reflection->getMethod('processLimit');

        $result = $method->invoke($this->decorator, $platformMock);

        self::assertSame([''], $result);
    }

    public function testProcessOffsetWithoutOffset(): void
    {
        $platformMock = $this->createMock(PlatformInterface::class);

        $reflection = new ReflectionClass($this->decorator);
        $method     = $reflection->getMethod('processOffset');

        $result = $method->invoke($this->decorator, $platformMock);

        self::assertNull($result);
    }

    public function testProcessOffsetWithOffset(): void
    {
        $this->decorator->offset(20);

        $platformMock = $this->createMock(PlatformInterface::class);

        $reflection = new ReflectionClass($this->decorator);
        $method     = $reflection->getMethod('processOffset');

        $result = $method->invoke($this->decorator, $platformMock);

        self::assertSame([20], $result);
    }

    public function testProcessOffsetWithOffsetAndParameterContainer(): void
    {
        $this->decorator->offset(25);

        $platformMock = $this->createMock(PlatformInterface::class);
        $driverMock   = $this->createMock(DriverInterface::class);
        $driverMock->expects(self::once())
            ->method('formatParameterName')
            ->with('offset')
            ->willReturn(':offset');

        $parameterContainer = new ParameterContainer();

        $reflection          = new ReflectionClass($this->decorator);
        $processInfoProperty = $reflection->getProperty('processInfo');
        $processInfoProperty->setValue($this->decorator, ['paramPrefix' => '']);

        $method = $reflection->getMethod('processOffset');
        $result = $method->invoke($this->decorator, $platformMock, $driverMock, $parameterContainer);

        self::assertSame([':offset'], $result);
        self::assertTrue($parameterContainer->offsetExists('offset'));
        self::assertSame(25, $parameterContainer->offsetGet('offset'));
    }
}
