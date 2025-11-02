<?php

declare(strict_types=1);

namespace PhpDb\Adapter\Sqlite;

/**
 * @psalm-api
 */
final class Module
{
    public function getConfig(): array
    {
        return [
            'service_manager' => (new ConfigProvider())->getDependencies(),
        ];
    }
}
