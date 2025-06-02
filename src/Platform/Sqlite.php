<?php

namespace Laminas\Db\Sqlite\Platform;

use Laminas\Db\Adapter\Driver\DriverInterface;
use Laminas\Db\Adapter\Exception;
use Laminas\Db\Adapter\Platform\AbstractPlatform;
use Laminas\Db\Sql\Platform\PlatformDecoratorInterface;
use Laminas\Db\Sqlite\Driver\Pdo\Driver;
use Laminas\Db\Sqlite\Sql\Platform\Sqlite as SqlPlatformDecorator;
use PDO;

class Sqlite extends AbstractPlatform
{
    /** @var string[] */
    protected $quoteIdentifier = ['"', '"'];

    /**
     * {@inheritDoc}
     */
    protected $quoteIdentifierTo = '\'';

    /** @var Driver|PDO|null */
    protected Driver|PDO|null $resource = null;

    public function __construct(Driver|PDO|null $driver = null)
    {
        if ($driver) {
            $this->setDriver($driver);
        }
    }

    /**
     * @throws Exception\InvalidArgumentException
     * @return $this Provides a fluent interface
     */
    public function setDriver(PDO|Driver $driver): static
    {
        if (
            (
                $driver instanceof PDO
                && $driver->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite'
            )
            || (
                $driver instanceof Driver
                && $driver->getDatabasePlatformName() === 'Sqlite'
            )
        ) {
            $this->resource = $driver;

            return $this;
        }

        throw new Exception\InvalidArgumentException(
            '$driver must be a Sqlite PDO Laminas\Db\Adapter\Driver, Sqlite PDO instance'
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return 'SQLite';
    }

    /**
     * {@inheritDoc}
     */
    public function quoteValue($value)
    {
        $resource = $this->resource;

        if ($resource instanceof DriverInterface) {
            $resource = $resource->getConnection()->getResource();
        }

        if ($resource instanceof PDO) {
            return $resource->quote($value);
        }

        return parent::quoteValue($value);
    }

    /**
     * {@inheritDoc}
     */
    public function quoteTrustedValue($value)
    {
        $resource = $this->resource;

        if ($resource instanceof DriverInterface) {
            $resource = $resource->getConnection()->getResource();
        }

        if ($resource instanceof PDO) {
            return $resource->quote($value);
        }

        return parent::quoteTrustedValue($value);
    }

    public function getSqlPlatformDecorator(): PlatformDecoratorInterface
    {
        return new SqlPlatformDecorator();
    }
}
