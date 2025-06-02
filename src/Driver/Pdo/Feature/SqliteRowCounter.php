<?php

namespace Laminas\Db\Sqlite\Driver\Pdo\Feature;

use Closure;
use Laminas\Db\Adapter\Driver\Feature\AbstractFeature;
use Laminas\Db\Adapter\Driver\Pdo;
use Laminas\Db\Adapter\Driver\Pdo\Statement;

use function stripos;

/**
 * SqliteRowCounter
 */
class SqliteRowCounter extends AbstractFeature
{
    public function getName(): string
    {
        return 'SqliteRowCounter';
    }

    public function getCountForStatement(Pdo\Statement $statement): ?int
    {
        $countStmt = clone $statement;
        $sql       = $statement->getSql();
        if ($sql === '' || stripos($sql, 'select') === false) {
            return null;
        }
        $countSql = 'SELECT COUNT(*) as "count" FROM (' . $sql . ')';
        $countStmt->prepare($countSql);
        $result   = $countStmt->execute();
        $countRow = $result->getResource()->fetch(\PDO::FETCH_ASSOC);
        unset($statement, $result);

        return $countRow['count'];
    }

    public function getCountForSql(string $sql): ?int
    {
        if (stripos($sql, 'select') === false) {
            return null;
        }
        $countSql = 'SELECT COUNT(*) as count FROM (' . $sql . ')';
        /** @var \PDO $pdo */
        $pdo      = $this->driver->getConnection()->getResource();
        $result   = $pdo->query($countSql);
        $countRow = $result->fetch(\PDO::FETCH_ASSOC);

        return $countRow['count'];
    }

    public function getRowCountClosure(Statement|string $context): Closure
    {
        return function () use ($context) {
            return $context instanceof Pdo\Statement
                ? $this->getCountForStatement($context)
                : $this->getCountForSql($context);
        };
    }
}
