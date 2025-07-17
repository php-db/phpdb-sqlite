<?php

declare(strict_types=1);

namespace PhpDb\Adapter\Sqlite\Driver\Pdo;

use Override;
use PDOException;
use PDOStatement;
use PhpDb\Adapter\Driver\ConnectionInterface;
use PhpDb\Adapter\Driver\Pdo\AbstractPdoConnection;
use PhpDb\Adapter\Exception;

use function array_diff_key;
use function implode;
use function is_int;
use function is_string;
use function strtolower;

class Connection extends AbstractPdoConnection
{
    /**
     * {@inheritDoc}
     */
    #[Override]
    public function getCurrentSchema(): string|bool
    {
        if (! $this->isConnected()) {
            $this->connect();
        }

        /** @var PDOStatement $result */
        $result = $this->resource->query('PRAGMA database_list');
        if ($result instanceof PDOStatement) {
            return $result->fetchColumn();
        }

        return false;
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception\InvalidConnectionParametersException
     * @throws Exception\RuntimeException
     *
     * This connection class only supports the 'dsn', 'dir', or 'path' parameters.
     * If 'dsn' is not provided, it will attempt to construct one from 'dir' or 'path'.
     * If neither is provided, an exception will be thrown.
     * If 'driver_options' is provided, it will merge with existing options.
     */
    #[Override]
    public function connect(): ConnectionInterface
    {
        if ($this->resource) {
            return $this;
        }

        $dsn     = null;
        $options = [];
        foreach ($this->connectionParameters as $key => $value) {
            switch (strtolower($key)) {
                case 'dsn':
                    $dsn = $value;
                    break;
                case 'dir':
                case 'path':
                    $path = (string) $value;
                    break;
                case 'driver_options':
                    $value   = (array) $value;
                    $options = array_diff_key($options, $value) + $value;
                    break;
                default:
                    //$options[$key] = $value;
                    break;
            }
        }

        if (! isset($dsn)) {
            $dsn = [];
            if (isset($path)) {
                $dsn[] = $path;
            }
            $dsn = 'sqlite:' . implode('', $dsn);
        }

        if (! is_string($dsn)) {
            throw new Exception\InvalidConnectionParametersException(
                'A dsn was not provided or could not be constructed from your parameters',
                $this->connectionParameters
            );
        }

        $this->dsn = $dsn;

        try {

            $this->resource = new \PDO($dsn);
            $this->resource->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $this->driverName = strtolower($this->resource->getAttribute(\PDO::ATTR_DRIVER_NAME));
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
     *
     * @param string $name
     */
    #[Override]
    public function getLastGeneratedValue($name = null): string|int|bool|null
    {
        try {
            return $this->resource->lastInsertId($name);
        } catch (\Exception) {
        }

        return false;
    }
}
