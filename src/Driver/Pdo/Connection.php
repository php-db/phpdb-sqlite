<?php

namespace Laminas\Db\Sqlite\Driver\Pdo;

use Laminas\Db\Adapter\Driver\Pdo\AbstractPdoConnection;
use Laminas\Db\Adapter\Driver\PdoDriverInterface;
use Laminas\Db\Adapter\Driver\ResultInterface;
use Laminas\Db\Adapter\Driver\StatementInterface;
use Laminas\Db\Adapter\Exception;
use Laminas\Db\Adapter\Exception\RunTimeException;
use PDO;
use PDOException;
use PDOStatement;

use function array_diff_key;
use function is_array;
use function is_int;
use function str_replace;
use function strtolower;
use function substr;

class Connection extends AbstractPdoConnection
{
    /** @var PdoDriverInterface */
    protected PdoDriverInterface $driver;

    /** @var PDO */
    protected $resource;

    /** @var null|string */
    protected ?string $dsn;

    /**
     * Constructor
     *
     * @param PDO|array|null $connectionParameters
     * @throws Exception\InvalidArgumentException
     */
    public function __construct(PDO|array $connectionParameters = null)
    {
        parent::__construct($connectionParameters);

        if (is_array($connectionParameters)) {
            $this->setConnectionParameters($connectionParameters);
        } elseif ($connectionParameters instanceof PDO) {
            $this->setResource($connectionParameters);
        } elseif (null !== $connectionParameters) {
            throw new Exception\InvalidArgumentException(
                '$connection must be an array of parameters, a PDO object or null'
            );
        }
    }

    /**
     * Set driver
     *
     * @return $this Provides a fluent interface
     */
    public function setDriver(PdoDriverInterface $driver): static
    {
        $this->driver = $driver;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getCurrentSchema(): bool|string
    {
        if (! $this->isConnected()) {
            $this->connect();
        }

        /** @var PDOStatement $result */
        $result = $this->resource->query('main');
        if ($result instanceof PDOStatement) {
            return $result->fetchColumn();
        }

        return false;
    }

    /**
     * {@inheritDoc}
     * @throws Exception\InvalidConnectionParametersException
     * @throws Exception\RuntimeException
     */
    public function connect(): static
    {
        if ($this->resource) {
            return $this;
        }

        $dsn     = $username = $password = $hostname = $database = null;
        $options = [];
        foreach ($this->connectionParameters as $key => $value) {
            switch (strtolower($key)) {
                case 'dsn':
                    $dsn = $value;
                    break;
                case 'driver':
                    $value = strtolower((string) $value);
                    if (str_starts_with($value, 'pdo')) {
                        $pdoDriver = str_replace(['-', '_', ' '], '', $value);
                        $pdoDriver = substr($pdoDriver, 3) ?: '';
                    }
                    break;
                case 'pdodriver':
                    $pdoDriver = (string) $value;
                    break;
                case 'user':
                case 'username':
                    $username = (string) $value;
                    break;
                case 'pass':
                case 'password':
                    $password = (string) $value;
                    break;
                case 'host':
                case 'hostname':
                    $hostname = (string) $value;
                    break;
                case 'database':
                case 'dbname':
                    $database = (string) $value;
                    break;
                case 'unix_socket':
                    $unixSocket = (string) $value;
                    break;
                case 'driver_options':
                case 'options':
                    $value   = (array) $value;
                    $options = array_diff_key($options, $value) + $value;
                    break;
                default:
                    $options[$key] = $value;
                    break;
            }
        }

        if (isset($hostname) && isset($unixSocket)) {
            throw new Exception\InvalidConnectionParametersException(
                'Ambiguous connection parameters, both hostname and unix_socket parameters were set',
                $this->connectionParameters
            );
        }

        if (! isset($dsn) && isset($pdoDriver)) {
            $dsn = $pdoDriver . ':' . $database;
        } elseif (! isset($dsn)) {
            throw new Exception\InvalidConnectionParametersException(
                'A dsn was not provided or could not be constructed from your parameters',
                $this->connectionParameters
            );
        }

        $this->dsn = $dsn;

        try {
            $this->resource = new PDO($dsn, $username, $password, $options);
            $this->resource->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->driverName = strtolower($this->resource->getAttribute(PDO::ATTR_DRIVER_NAME));
        } catch (PDOException $e) {
            $code = $e->getCode();
            if (! is_int($code)) {
                $code = 0;
            }
            throw new Exception\RuntimeException('Connect Error: ' . $e->getMessage(), $code, $e);
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function isConnected(): bool
    {
        return $this->resource instanceof PDO;
    }

    /**
     * {@inheritDoc}
     */
    public function beginTransaction(): static
    {
        if (! $this->isConnected()) {
            $this->connect();
        }

        if (0 === $this->nestedTransactionsCount) {
            $this->resource->beginTransaction();
            $this->inTransaction = true;
        }

        $this->nestedTransactionsCount++;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function commit(): static
    {
        if (! $this->isConnected()) {
            $this->connect();
        }

        if ($this->inTransaction) {
            $this->nestedTransactionsCount -= 1;
        }

        /*
         * This shouldn't check for being in a transaction since
         * after issuing a SET autocommit=0; we have to commit too.
         */
        if (0 === $this->nestedTransactionsCount) {
            $this->resource->commit();
            $this->inTransaction = false;
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     * @throws Exception\RuntimeException
     */
    public function rollback(): static
    {
        if (! $this->isConnected()) {
            throw new Exception\RuntimeException('Must be connected before you can rollback');
        }

        if (! $this->inTransaction()) {
            throw new Exception\RuntimeException('Must call beginTransaction() before you can rollback');
        }

        $this->resource->rollBack();

        $this->inTransaction           = false;
        $this->nestedTransactionsCount = 0;

        return $this;
    }

    /**
     * {@inheritDoc}
     * @throws Exception\InvalidQueryException
     */
    public function execute($sql): ResultInterface
    {
        if (! $this->isConnected()) {
            $this->connect();
        }

        $this->profiler?->profilerStart($sql);

        $resultResource = $this->resource->query($sql);

        $this->profiler?->profilerFinish();

        if ($resultResource === false) {
            $errorInfo = $this->resource->errorInfo();
            throw new Exception\InvalidQueryException($errorInfo[2]);
        }

        return $this->driver->createResult($resultResource, $sql);
    }

    /**
     * {@inheritDoc}
     * @param string $name
     * @return string|null|false
     */
    public function getLastGeneratedValue($name = null): bool|int|string|null
    {
        try {
            return $this->resource->lastInsertId($name);
        } catch (\Exception) {
        }

        return false;
    }

    /**
     * Get the dsn string for this connection
     *
     * @throws RunTimeException
     * @return string
     */
    public function getDsn(): string
    {
        if (! $this->dsn) {
            throw new Exception\RuntimeException(
                'The DSN has not been set or constructed from parameters in connect() for this Connection'
            );
        }

        return $this->dsn;
    }

    /**
     * Set resource
     *
     * @return $this Provides a fluent interface
     */
    public function setResource(PDO $resource): static
    {
        $this->resource   = $resource;
        $this->driverName = strtolower($this->resource->getAttribute(PDO::ATTR_DRIVER_NAME));

        return $this;
    }

    /**
     * Prepare
     *
     * @param ?string $sql
     * @return StatementInterface
     */
    public function prepare(?string $sql = null): StatementInterface
    {
        if (! $this->isConnected()) {
            $this->connect();
        }

        return $this->driver->createStatement($sql);
    }
}
