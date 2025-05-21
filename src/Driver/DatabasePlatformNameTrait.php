<?php

declare(strict_types=1);

namespace Laminas\Db\Sqlite\Driver;

trait DatabasePlatformNameTrait
{
    /**
     * Get database platform name
     *
     * @param string $nameFormat
     * @return string
     */
    public function getDatabasePlatformName(string $nameFormat = self::NAME_FORMAT_CAMELCASE): string
    {
        if ($nameFormat === self::NAME_FORMAT_CAMELCASE) {
            return 'Sqlite';
        }

        return 'SQLite';
    }
}
