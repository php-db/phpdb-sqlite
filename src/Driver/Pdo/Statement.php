<?php

namespace Laminas\Db\Sqlite\Driver\Pdo;

use Laminas\Db\Adapter\Driver\DriverInterface;
use Laminas\Db\Adapter\Driver\ResultInterface;
use Laminas\Db\Adapter\Driver\StatementInterface;
use Laminas\Db\Adapter\Exception;
use Laminas\Db\Adapter\ParameterContainer;
use Laminas\Db\Adapter\Profiler;
use PDO;
use PDOException;
use PDOStatement;

use function implode;
use function is_array;
use function is_bool;
use function is_int;

class Statement implements StatementInterface, Profiler\ProfilerAwareInterface
{
    /** @var PDO */
    protected PDO $pdo;

    /** @var ?Profiler\ProfilerInterface */
    protected ?Profiler\ProfilerInterface $profiler;

    /** @var DriverInterface */
    protected DriverInterface $driver;

    /** @var string */
    protected string $sql = '';

    /** @var bool */
    protected bool $isQuery;

    /** @var ?ParameterContainer */
    protected ?ParameterContainer $parameterContainer = null;

    /** @var bool */
    protected bool $parametersBound = false;

    /** @var ?PDOStatement */
    protected ?PDOStatement $resource = null;

    /** @var bool */
    protected bool $isPrepared = false;

    /**
     * Set driver
     *
     * @return $this Provides a fluent interface
     */
    public function setDriver(DriverInterface $driver): static
    {
        $this->driver = $driver;
        return $this;
    }

    /**
     * @return $this Provides a fluent interface
     */
    public function setProfiler(Profiler\ProfilerInterface $profiler): static
    {
        $this->profiler = $profiler;
        return $this;
    }

    /**
     * @return null|Profiler\ProfilerInterface
     */
    public function getProfiler(): ?Profiler\ProfilerInterface
    {
        return $this->profiler;
    }

    /**
     * Initialize
     *
     * @return $this Provides a fluent interface
     */
    public function initialize(PDO $connectionResource): static
    {
        $this->pdo = $connectionResource;
        return $this;
    }

    /**
     * Set resource
     *
     * @return $this Provides a fluent interface
     */
    public function setResource(PDOStatement $pdoStatement): static
    {
        $this->resource = $pdoStatement;
        return $this;
    }

    /**
     * Get resource
     *
     * @return PDOStatement
     */
    public function getResource(): PDOStatement
    {
        return $this->resource;
    }

    /**
     * Set sql
     *
     * @param string $sql
     * @return $this Provides a fluent interface
     */
    public function setSql($sql): static
    {
        $this->sql = $sql;
        return $this;
    }

    /**
     * Get sql
     *
     * @return string|null
     */
    public function getSql(): ?string
    {
        return $this->sql;
    }

    /**
     * @return $this Provides a fluent interface
     */
    public function setParameterContainer(ParameterContainer $parameterContainer): static
    {
        $this->parameterContainer = $parameterContainer;
        return $this;
    }

    /**
     * @return ParameterContainer
     */
    public function getParameterContainer(): ParameterContainer
    {
        return $this->parameterContainer;
    }

    /**
     * @param string $sql
     * @throws Exception\RuntimeException
     */
    public function prepare($sql = null): static
    {
        if ($this->isPrepared) {
            throw new Exception\RuntimeException('This statement has been prepared already');
        }

        if ($sql === null) {
            $sql = $this->sql;
        }

        $this->resource = $this->pdo->prepare($sql);

        if ($this->resource === false) {
            $error = $this->pdo->errorInfo();
            throw new Exception\RuntimeException($error[2]);
        }

        $this->isPrepared = true;

        return $this;
    }

    /**
     * @return bool
     */
    public function isPrepared(): bool
    {
        return $this->isPrepared;
    }

    /**
     * @param null|array|ParameterContainer $parameters
     * @throws Exception\InvalidQueryException
     * @return ResultInterface
     */
    public function execute($parameters = null): ResultInterface
    {
        if (! $this->isPrepared) {
            $this->prepare();
        }

        /** START Standard ParameterContainer Merging Block */
        if (! $this->parameterContainer instanceof ParameterContainer) {
            if ($parameters instanceof ParameterContainer) {
                $this->parameterContainer = $parameters;
                $parameters               = null;
            } else {
                $this->parameterContainer = new ParameterContainer();
            }
        }

        if (is_array($parameters)) {
            $this->parameterContainer->setFromArray($parameters);
        }

        if ($this->parameterContainer->count() > 0) {
            $this->bindParametersFromContainer();
        }
        /** END Standard ParameterContainer Merging Block */

        $this->profiler?->profilerStart($this);

        try {
            $this->resource->execute();
        } catch (PDOException $e) {
            $this->profiler?->profilerFinish();

            $code = $e->getCode();
            if (! is_int($code)) {
                $code = 0;
            }

            throw new Exception\InvalidQueryException(
                'Statement could not be executed (' . implode(' - ', $this->resource->errorInfo()) . ')',
                $code,
                $e
            );
        }

        $this->profiler?->profilerFinish();

        return $this->driver->createResult($this->resource, $this);
    }

    /**
     * Bind parameters from container
     */
    protected function bindParametersFromContainer(): void
    {
        if ($this->parametersBound) {
            return;
        }

        $parameters = $this->parameterContainer->getNamedArray();
        foreach ($parameters as $name => &$value) {
            if (is_bool($value)) {
                $type = PDO::PARAM_BOOL;
            } elseif (is_int($value)) {
                $type = PDO::PARAM_INT;
            } else {
                $type = PDO::PARAM_STR;
            }
            if ($this->parameterContainer->offsetHasErrata($name)) {
                switch ($this->parameterContainer->offsetGetErrata($name)) {
                    case ParameterContainer::TYPE_INTEGER:
                        $type = PDO::PARAM_INT;
                        break;
                    case ParameterContainer::TYPE_NULL:
                        $type = PDO::PARAM_NULL;
                        break;
                    case ParameterContainer::TYPE_LOB:
                        $type = PDO::PARAM_LOB;
                        break;
                }
            }

            // parameter is named or positional, value is reference
            $parameter = is_int($name) ? $name + 1 : $this->driver->formatParameterName($name);
            $this->resource->bindParam($parameter, $value, $type);
        }
    }

    /**
     * Perform a deep clone
     *
     * @return void
     */
    public function __clone()
    {
        $this->isPrepared      = false;
        $this->parametersBound = false;
        $this->resource        = null;
        if ($this->parameterContainer) {
            $this->parameterContainer = clone $this->parameterContainer;
        }
    }
}
