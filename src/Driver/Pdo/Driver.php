<?php

namespace Laminas\Db\Sqlite\Driver\Pdo;

use Laminas\Db\Adapter\Driver\DriverInterface;
use Laminas\Db\Adapter\Driver\Feature\AbstractFeature;
use Laminas\Db\Adapter\Driver\Feature\DriverFeatureInterface;
use Laminas\Db\Adapter\Driver\Pdo\AbstractPdo;
use Laminas\Db\Adapter\Driver\StatementInterface;
use Laminas\Db\Adapter\Exception;
use Laminas\Db\Adapter\Profiler;
use Laminas\Db\Sqlite\Driver\DatabasePlatformNameTrait;
use PDO;
use PDOStatement;

use function extension_loaded;
use function is_array;
use function is_numeric;
use function is_string;
use function ltrim;
use function preg_match;
use function sprintf;

class Driver extends AbstractPdo implements DriverInterface, DriverFeatureInterface, Profiler\ProfilerAwareInterface
{
    use DatabasePlatformNameTrait;

    /**
     * @const
     */
    public const FEATURES_DEFAULT = 'default';

    /**
     * @internal
     * @var Profiler\ProfilerInterface
     */
    public $profiler;

    /** @var Connection */
    protected $connection;

    /** @var ?Statement */
    protected $statementPrototype;

    /** @var ?Result */
    protected $resultPrototype;

    /** @var array */
    protected $features = [];

    /**
     * @return null|Profiler\ProfilerInterface
     */
    public function getProfiler(): ?Profiler\ProfilerInterface
    {
        return $this->profiler;
    }

    /**
     * @return $this Provides a fluent interface
     */
    public function setProfiler(Profiler\ProfilerInterface $profiler): static
    {
        $this->profiler = $profiler;
        if ($this->connection instanceof Profiler\ProfilerAwareInterface) {
            $this->connection->setProfiler($profiler);
        }
        if ($this->statementPrototype instanceof Profiler\ProfilerAwareInterface) {
            $this->statementPrototype->setProfiler($profiler);
        }

        return $this;
    }

    /**
     * Register connection
     *
     * @return $this Provides a fluent interface
     */
    public function registerConnection(Connection $connection): static
    {
        $this->connection = $connection;
        $this->connection->setDriver($this);

        return $this;
    }

    /**
     * Register statement prototype
     */
    public function registerStatementPrototype(Statement $statementPrototype): void
    {
        $this->statementPrototype = $statementPrototype;
        $this->statementPrototype->setDriver($this);
    }

    /**
     * Register result prototype
     */
    public function registerResultPrototype(Result $resultPrototype): void
    {
        $this->resultPrototype = $resultPrototype;
    }

    /**
     * Add feature
     *
     * @param string          $name
     * @param AbstractFeature $feature
     * @return $this Provides a fluent interface
     */
    public function addFeature($name, $feature): static
    {
        if ($feature instanceof AbstractFeature) {
            $name = $feature->getName(); // overwrite the name, just in case
            $feature->setDriver($this);
        }
        $this->features[$name] = $feature;

        return $this;
    }

    /**
     * Setup the default features for Pdo
     *
     * @return $this Provides a fluent interface
     */
    public function setupDefaultFeatures()
    {
        $this->addFeature(null, new Feature\SqliteRowCounter());

        return $this;
    }

    /**
     * Get feature
     *
     * @param string $name
     * @return AbstractFeature|false
     */
    public function getFeature($name)
    {
        if (isset($this->features[$name])) {
            return $this->features[$name];
        }

        return false;
    }

    /**
     * Check environment
     */
    public function checkEnvironment()
    {
        if (! extension_loaded('PDO')) {
            throw new Exception\RuntimeException(
                'The PDO extension is required for this adapter but the extension is not loaded'
            );
        }
    }

    /**
     * @return Connection
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * @param resource $resource
     * @param mixed    $context
     * @return Result
     */
    public function createResult($resource, $context = null)
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

    /**
     * @return string
     */
    public function getPrepareType(): string
    {
        return self::PARAMETERIZATION_NAMED;
    }

    /**
     * @param string      $name
     * @param string|null $type
     * @return string
     */
    public function formatParameterName($name, $type = null)
    {
        if ($type === null && ! is_numeric($name) || $type === self::PARAMETERIZATION_NAMED) {
            $name = ltrim($name, ':');
            // @see https://bugs.php.net/bug.php?id=43130
            if (preg_match('/[^a-zA-Z0-9_]/', $name)) {
                throw new Exception\RuntimeException(sprintf(
                    'The PDO param %s contains invalid characters.'
                    . ' Only alphabetic characters, digits, and underscores (_)'
                    . ' are allowed.',
                    $name
                ));
            }

            return ':' . $name;
        }

        return '?';
    }

    /**
     * @param string|null $name
     * @return string|null|false
     */
    public function getLastGeneratedValue($name = null)
    {
        return $this->connection->getLastGeneratedValue($name);
    }

    /**
     * @return Result
     */
    public function getResultPrototype()
    {
        return $this->resultPrototype;
    }
}
