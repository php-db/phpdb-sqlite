<?php

declare(strict_types=1);

namespace PhpDb\Adapter\Sqlite\Container;

use PhpDb\Adapter\Sqlite\Container\PlatformInterfaceFactory;
use PhpDb\Container\PlatformInterfaceFactoryFactoryInterface as FactoryFactoryInterface;

final class PlatformInterfaceFactoryFactory implements FactoryFactoryInterface
{
    public function __invoke(): callable
    {
        return new PlatformInterfaceFactory();
    }
}
