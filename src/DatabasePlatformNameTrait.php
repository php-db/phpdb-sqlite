<?php

declare(strict_types=1);

namespace PhpDb\Adapter\Sqlite;

use PhpDb\Adapter\Driver\DriverInterface;
use PhpDb\Adapter\Exception;

trait DatabasePlatformNameTrait
{
    /**
     * Get database platform name
     */
    public function getDatabasePlatformName(
        string $nameFormat = DriverInterface::NAME_FORMAT_CAMELCASE
    ): string {
        if ($nameFormat === DriverInterface::NAME_FORMAT_CAMELCASE) {
            return 'Sqlite';
        }

        if ($nameFormat === DriverInterface::NAME_FORMAT_NATURAL) {
            return 'SQLite';
        }

        throw new Exception\InvalidArgumentException(
            'Invalid name format provided. Must be one of: '
            . DriverInterface::NAME_FORMAT_CAMELCASE
            . ', '
            . DriverInterface::NAME_FORMAT_NATURAL
        );
    }
}
