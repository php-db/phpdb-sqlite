<?php

declare(strict_types=1);

namespace PhpDb\Adapter\Sqlite\Platform;

use Override;
use PhpDb\Adapter\Driver\PdoDriverInterface;
use PhpDb\Adapter\Exception;
use PhpDb\Adapter\Platform\AbstractPlatform;
use PhpDb\Sql\Platform\PlatformDecoratorInterface;
use PhpDb\Adapter\Sqlite\Driver\Pdo;
use PhpDb\Adapter\Sqlite\Sql\Platform\Sqlite as SqlPlatformDecorator;

class Sqlite extends AbstractPlatform
{
    public final const PLATFORM_NAME = 'SQLite';
    /** @var string[] */
    protected $quoteIdentifier = ['"', '"'];

    /** @var \PDO */
    protected $resource;

    /**
     * {@inheritDoc}
     */
    protected $quoteIdentifierTo = '\'';

    public function __construct(
        protected readonly PdoDriverInterface|\PDO $driver
    ) {
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function quoteValue(string $value): string
    {
        $resource = $this->resource;

        if ($resource instanceof PdoDriverInterface) {
            $resource = $resource->getConnection()->getResource();
        }

        if ($resource instanceof \PDO) {
            return $resource->quote($value);
        }

        return parent::quoteValue($value);
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function quoteTrustedValue(int|float|string|bool $value): ?string
    {
        $resource = $this->resource;

        if ($resource instanceof PdoDriverInterface) {
            $resource = $resource->getConnection()->getResource();
        }

        if ($resource instanceof \PDO) {
            return $resource->quote($value);
        }

        return parent::quoteTrustedValue($value);
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function getName(): string
    {
        return self::PLATFORM_NAME;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function getSqlPlatformDecorator(): PlatformDecoratorInterface
    {
        return new SqlPlatformDecorator();
    }
}
