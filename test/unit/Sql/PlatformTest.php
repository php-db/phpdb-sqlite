<?php

declare(strict_types=1);

namespace PhpDbTest\Sqlite\Sql;

use PhpDb\Sql\Select;
use PhpDb\Sqlite\Sql\Platform;
use PhpDb\Sqlite\Sql\SelectDecorator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

#[CoversClass(Platform::class)]
final class PlatformTest extends TestCase
{
    private Platform $platform;

    protected function setUp(): void
    {
        $this->platform = new Platform();
    }

    public function testConstructorSetsTypeDecorators(): void
    {
        self::assertInstanceOf(Platform::class, $this->platform);
    }

    public function testSelectDecoratorIsRegistered(): void
    {
        $reflection         = new ReflectionClass($this->platform);
        $decoratorsProperty = $reflection->getProperty('decorators');
        $decorators         = $decoratorsProperty->getValue($this->platform);

        self::assertArrayHasKey(Select::class, $decorators);
        self::assertInstanceOf(SelectDecorator::class, $decorators[Select::class]);
    }
}
