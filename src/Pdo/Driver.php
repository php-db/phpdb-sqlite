<?php

declare(strict_types=1);

namespace PhpDb\Sqlite\Pdo;

use Override;
use PDOStatement;
use PhpDb\Adapter\Driver\Feature\DriverFeatureProviderInterface;
use PhpDb\Adapter\Driver\Feature\DriverFeatureProviderTrait;
use PhpDb\Adapter\Driver\Pdo\AbstractPdo;
use PhpDb\Adapter\Driver\Pdo\Result;
use PhpDb\Adapter\Driver\Pdo\Statement;
use PhpDb\Adapter\Driver\ResultInterface;
use PhpDb\Sqlite\DatabasePlatformNameTrait;

class Driver extends AbstractPdo implements DriverFeatureProviderInterface
{
    use DatabasePlatformNameTrait;
    use DriverFeatureProviderTrait;

    /**
     * @param PDOStatement|resource $resource
     */
    #[Override]
    public function createResult($resource, Statement|string|null $context = null): ResultInterface
    {
        /** @var ResultInterface&Result $result */
        $result = clone $this->resultPrototype;
        /** @var Feature\SqliteRowCounter $sqliteRowCounter */
        $sqliteRowCounter = $this->getFeature(Feature\SqliteRowCounter::class);
        $rowCount         = null;

        if ($sqliteRowCounter && $resource->columnCount() > 0) {
            $rowCount = $sqliteRowCounter->getRowCountClosure($context);
        }

        $result->initialize($resource, $this->connection->getLastGeneratedValue(), $rowCount);

        return $result;
    }
}
