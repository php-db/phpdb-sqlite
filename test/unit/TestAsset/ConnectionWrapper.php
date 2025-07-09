<?php

declare(strict_types=1);

namespace PhpDbTest\Adapter\Sqlite\TestAsset;

use PhpDb\Adapter\Sqlite\Driver\Pdo\Connection;

/**
 * Test asset class used only by {@see \PhpDbTest\Adapter\Sqlite\Adapter\Driver\Pdo\ConnectionTransactionsTest}
 */
final class ConnectionWrapper extends Connection
{
    public function __construct()
    {
        $this->resource = new PdoStubDriver('foo', 'bar', 'baz');
    }

    public function getNestedTransactionsCount(): int
    {
        return $this->nestedTransactionsCount;
    }
}
