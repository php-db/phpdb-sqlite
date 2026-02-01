<?php

declare(strict_types=1);

namespace PhpDbIntegrationTest\Sqlite;

use PhpDb\Sqlite\AdapterPlatform;
use PhpDbIntegrationTest\Sqlite\Container\TestAsset\SetupTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('integration')]
#[Group('integration-sqlite')]
#[CoversClass(AdapterPlatform::class)]
#[CoversMethod(AdapterPlatform::class, 'quoteValue')]
final class AdapterPlatformTest extends TestCase
{
    use SetupTrait;

    public function testQuoteValueWithPdoSqlite(): void
    {
        $sqlite = $this->getAdapter()->getPlatform();
        $value  = $sqlite->quoteValue('value');
        self::assertEquals('\'value\'', $value);
    }
}
