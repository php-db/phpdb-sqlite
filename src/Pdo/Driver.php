<?php

declare(strict_types=1);

namespace PhpDb\Sqlite\Pdo;

use Override;
use PDO;
use PDOStatement;
use PhpDb\Adapter\Driver\Feature\DriverFeatureProviderInterface;
use PhpDb\Adapter\Driver\Feature\DriverFeatureProviderTrait;
use PhpDb\Adapter\Driver\Pdo\AbstractPdo;
use PhpDb\Adapter\Driver\Pdo\Result;
use PhpDb\Adapter\Driver\Pdo\Statement;
use PhpDb\Adapter\Driver\PdoConnectionInterface;
use PhpDb\Adapter\Driver\PdoDriverAwareInterface;
use PhpDb\Adapter\Driver\ResultInterface;
use PhpDb\Adapter\Driver\StatementInterface;

class Driver extends AbstractPdo implements DriverFeatureProviderInterface
{
    use DriverFeatureProviderTrait;

    public function __construct(
        (PdoConnectionInterface&PdoDriverAwareInterface)|PDO $connection,
        (StatementInterface&PdoDriverAwareInterface)|Statement $statementPrototype = new Statement(),
        ResultInterface $resultPrototype = new Result(),
        array $features = [],
    ) {
        $this->connection         = $connection;
        $this->statementPrototype = $statementPrototype;
        $this->resultPrototype    = $resultPrototype;

        if (! $this->connection instanceof PDO) {
            $this->connection->setDriver($this);
        }

        $this->statementPrototype->setDriver($this);

        // $features is not constructor promoted because $this->features is defined in the trait
        if ($features !== []) {
            $this->addFeatures($features);
        }
    }

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
        $rowCount         = 0;

        if ($sqliteRowCounter && $resource->columnCount() > 0) {
            $rowCount = $sqliteRowCounter->getRowCountClosure($context);
        }

        $result->initialize($resource, $this->connection->getLastGeneratedValue(), $rowCount);

        return $result;
    }
}
