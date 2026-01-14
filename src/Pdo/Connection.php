<?php

declare(strict_types=1);

namespace PhpDb\Adapter\Sqlite\Pdo;

use Override;
use PDO;
use PDOException;
use PhpDb\Adapter\Driver\ConnectionInterface;
use PhpDb\Adapter\Driver\Pdo\AbstractPdoConnection;
use PhpDb\Adapter\Exception;
use Webmozart\Assert\Assert;

use function array_diff_key;
use function assert;
use function is_int;
use function is_string;
use function str_starts_with;
use function strtolower;

class Connection extends AbstractPdoConnection
{
    public final const CURRENT_SCHEMA = 'main';

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function getCurrentSchema(): string|false
    {
        if (! $this->isConnected()) {
            $this->connect();
        }

        return self::CURRENT_SCHEMA;
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception\InvalidConnectionParametersException
     * @throws Exception\RuntimeException
     *
     * This connection class only supports the 'dsn', or 'path' parameters.
     * If 'dsn' is not provided, it will attempt to construct one from 'path'.
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
                case 'driver_options':
                    $value   = (array) $value;
                    $options = array_diff_key($options, $value) + $value;
                    break;
                default:
                    $options[$key] = $value;
                    break;
            }
        }

        if (! is_string($dsn)) {
            throw new Exception\InvalidConnectionParametersException(
                'A dsn was not provided',
                $this->connectionParameters
            );
        }

        if (! str_starts_with($dsn, 'sqlite:')) {
            Assert::fileExists(
                $dsn,
                'The provided DSN does not point to a valid file.'
            );
            Assert::readable(
                $dsn,
                'The provided DSN does not point to a readable file.'
            );
            Assert::writable(
                $dsn,
                'The provided DSN does not point to a writable file.'
            );
            $dsn = 'sqlite:' . $dsn;
        }

        $this->dsn = $dsn;

        try {
            $this->resource = new PDO(dsn: $dsn, options: $options);
            $this->resource->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $driverName = $this->resource->getAttribute(PDO::ATTR_DRIVER_NAME);
            assert(is_string($driverName));
            $this->driverName = strtolower($driverName);
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
    public function getLastGeneratedValue($name = null): string|int|false|null
    {
        try {
            return $this->resource->lastInsertId($name);
        } catch (\Exception) {
        }

        return false;
    }
}
