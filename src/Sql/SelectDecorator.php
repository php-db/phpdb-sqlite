<?php

declare(strict_types=1);

namespace PhpDb\Sqlite\Sql;

use PhpDb\Adapter\Driver\DriverInterface;
use PhpDb\Adapter\ParameterContainer;
use PhpDb\Adapter\Platform\PlatformInterface;
use PhpDb\Sql\Platform\PlatformDecoratorInterface;
use PhpDb\Sql\PreparableSqlInterface;
use PhpDb\Sql\Select;
use PhpDb\Sql\SqlInterface;

final class SelectDecorator extends Select implements PlatformDecoratorInterface
{
    public SqlInterface|PreparableSqlInterface|null $subject;

    /**
     * @param Select $subject
     */
    public function setSubject($subject): static
    {
        $this->subject = $subject;

        return $this;
    }

    protected function localizeVariables(): void
    {
        parent::localizeVariables();
        if ($this->limit === null && $this->offset !== null) {
            $this->specifications[self::LIMIT] = 'LIMIT 18446744073709551615';
        }
    }

    /** @return null|string[]|int[] */
    protected function processLimit(
        PlatformInterface $platform,
        DriverInterface|null $driver = null,
        ParameterContainer|null $parameterContainer = null
    ): ?array {
        if ($this->limit === null && $this->offset !== null) {
            return [''];
        }
        if ($this->limit === null) {
            return null;
        }
        if ($parameterContainer) {
            $paramPrefix = (string) $this->processInfo['paramPrefix'];
            $parameterContainer->offsetSet($paramPrefix . 'limit', $this->limit, ParameterContainer::TYPE_INTEGER);
            return $driver !== null
                ? [$driver->formatParameterName($paramPrefix . 'limit')]
                : null;
        }

        return [$this->limit];
    }

    protected function processOffset(
        PlatformInterface $platform,
        DriverInterface|null $driver = null,
        ParameterContainer|null $parameterContainer = null
    ): ?array {
        if ($this->offset === null) {
            return null;
        }
        if ($parameterContainer) {
            $paramPrefix = (string) $this->processInfo['paramPrefix'];
            $parameterContainer->offsetSet($paramPrefix . 'offset', $this->offset, ParameterContainer::TYPE_INTEGER);
            return [$driver?->formatParameterName($paramPrefix . 'offset') ?? ''];
        }

        return [$this->offset];
    }
}
