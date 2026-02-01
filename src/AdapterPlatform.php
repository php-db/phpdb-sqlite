<?php

declare(strict_types=1);

namespace PhpDb\Sqlite;

use Override;
use PDO;
use PhpDb\Adapter\Driver\PdoDriverInterface;
use PhpDb\Adapter\Platform\AbstractPlatform;
use PhpDb\Sql\Platform\PlatformDecoratorInterface;
use PhpDb\Sqlite\Sql\Platform;

class AdapterPlatform extends AbstractPlatform
{
    public final const PLATFORM_NAME = 'SQLite';
    /** @var string[] */

    protected array $quoteIdentifier = ['"', '"'];

    /** @var PDO */
    protected $resource;

    /**
     * {@inheritDoc}
     */
    protected string $quoteIdentifierTo = '\'';

    public function __construct(
        protected readonly PdoDriverInterface|PDO|null $driver = null
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

        if ($resource instanceof PDO) {
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

        if ($resource instanceof PDO) {
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
        return new Platform();
    }
}
