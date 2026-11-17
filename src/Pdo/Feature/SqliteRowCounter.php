<?php

declare(strict_types=1);

namespace PhpDb\Sqlite\Pdo\Feature;

use Closure;
use PDOException;
use PDOStatement;
use PhpDb\Adapter\Driver\Feature\AbstractFeature;
use PhpDb\Adapter\Driver\Pdo;
use PhpDb\Adapter\Driver\Pdo\Statement;
use PhpDb\Adapter\Exception\RuntimeException;

use function str_contains;
use function strtolower;

/**
 * SqliteRowCounter
 */
class SqliteRowCounter extends AbstractFeature
{
    /**
     * @throws PDOException
     * @throws RuntimeException
     */
    public function getCountForSql(string $sql): int
    {
        if (empty($sql) || ! str_contains(strtolower($sql), 'select')) {
            return 0;
        }
        $countSql = 'SELECT COUNT(*) as count FROM (' . $sql . ')';
        /** @var \PDO $pdo */
        $pdo = $this->driver->getConnection()->getResource();

        return $this->fetchCount($pdo->query($countSql));
    }

    /**
     * @throws PDOException
     * @throws RuntimeException
     */
    public function getCountForStatement(Pdo\Statement $statement): int
    {
        $countStmt = clone $statement;
        $sql       = $statement->getSql();
        if (empty($sql) || ! str_contains(strtolower($sql), 'select')) {
            return 0;
        }
        $countSql = 'SELECT COUNT(*) as "count" FROM (' . $sql . ')';
        $countStmt->prepare($countSql);
        $result = $countStmt->execute();
        $count  = $this->fetchCount($result->getResource());
        unset($statement, $result);

        return $count;
    }

    public function getRowCountClosure(Statement|string|null $context): Closure
    {
        return (
            /**
             * @throws PDOException
             * @throws RuntimeException
             */
            function () use ($context): int {
                return $context instanceof Pdo\Statement
                    ? $this->getCountForStatement($context)
                    : $this->getCountForSql($context);
            }
        );
    }

    /**
     * @throws PDOException
     * @throws RuntimeException
     */
    private function fetchCount(mixed $statement): int
    {
        if (! $statement instanceof PDOStatement) {
            throw new RuntimeException('Unable to fetch the row count: the count query did not produce a statement');
        }
        $countRow = $statement->fetch(\PDO::FETCH_ASSOC);

        return (int) $countRow['count'];
    }
}
