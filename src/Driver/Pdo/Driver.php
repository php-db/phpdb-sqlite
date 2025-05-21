<?php

namespace Laminas\Db\Sqlite\Driver\Pdo;

use Laminas\Db\Adapter\Driver\DriverInterface;
use Laminas\Db\Adapter\Driver\Feature\DriverFeatureInterface;
use Laminas\Db\Adapter\Driver\Pdo\AbstractPdo;
use Laminas\Db\Adapter\Driver\Pdo\Result;
use Laminas\Db\Adapter\Driver\Pdo\Statement;
use Laminas\Db\Adapter\Profiler;
use Laminas\Db\Sqlite\Driver\DatabasePlatformNameTrait;

class Driver extends AbstractPdo implements DriverInterface, DriverFeatureInterface, Profiler\ProfilerAwareInterface
{
    use DatabasePlatformNameTrait;

    public function __construct(
        ?Connection $connection = null,
        ?Statement $statementPrototype = null,
        ?Result $resultPrototype = null,
        $features = self::FEATURES_DEFAULT
    ) {
        if ($connection === null) {
            $connection = new Connection();
        }

        parent::__construct($connection, $statementPrototype, $resultPrototype, $features);
    }

    /**
     * Register statement prototype
     */
    public function registerStatementPrototype(Statement $statementPrototype): void
    {
        $this->statementPrototype = $statementPrototype->setDriver($this);
    }

    /**
     * Register result prototype
     */
    public function registerResultPrototype(Result $resultPrototype): void
    {
        $this->resultPrototype = $resultPrototype;
    }

    /**
     * Setup the default features for Pdo
     *
     * @return $this Provides a fluent interface
     */
    public function setupDefaultFeatures(): static
    {
        $this->addFeature(null, new Feature\SqliteRowCounter());

        return $this;
    }

    /**
     * Register connection
     *
     * @return $this Provides a fluent interface
     */
    public function registerConnection(Connection $connection): static
    {
        $this->connection = $connection->setDriver($this);

        return $this;
    }

    #[\Override]
    /**
     * @param resource $resource
     * @param mixed    $context
     * @return \Laminas\Db\Adapter\Driver\Pdo\Result
     */
    public function createResult($resource, $context = null): \Laminas\Db\Adapter\Driver\Pdo\Result
    {
        $result           = clone $this->resultPrototype;
        $sqliteRowCounter = $this->getFeature('SqliteRowCounter');
        $rowCount         = null;

        if ($sqliteRowCounter && $resource->columnCount() > 0) {
            $rowCount = $sqliteRowCounter->getRowCountClosure($context);
        }

        $result->initialize($resource, $this->connection->getLastGeneratedValue(), $rowCount);

        return $result;
    }
}
