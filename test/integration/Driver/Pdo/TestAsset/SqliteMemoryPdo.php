<?php

namespace PhpDbIntegrationTest\Adapter\Sqlite\Driver\Pdo\TestAsset;

use Exception;
use PDO;
use PDOStatement;
use PHPUnit\Framework\MockObject\MockObject;

use function implode;
use function sprintf;

final class SqliteMemoryPdo extends PDO
{
    /**
     * @param string|null $sql
     * @throws Exception
     */
    public function __construct($sql = null)
    {
        parent::__construct('sqlite::memory:');

        if ($sql === '' || $sql === null) {
            return;
        }

        if (false === $this->exec($sql)) {
            throw new Exception(sprintf(
                "Error: %s, %s",
                $this->errorCode() ?? '',
                implode(",", $this->errorInfo())
            ));
        }
    }
}
