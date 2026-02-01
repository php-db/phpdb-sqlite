<?php

declare(strict_types=1);

namespace PhpDb\Sqlite\Container;

use PhpDb\Adapter\AdapterInterface;
use PhpDb\Adapter\SchemaAwareInterface;
use PhpDb\Metadata\MetadataInterface;
use PhpDb\Sqlite\Metadata;
use Psr\Container\ContainerInterface;

final class MetadataInterfaceFactory
{
    public function __invoke(ContainerInterface $container): MetadataInterface&Metadata\Source
    {
        /** @var AdapterInterface&SchemaAwareInterface $adapter */
        $adapter = $container->get(AdapterInterface::class);

        return new Metadata\Source(
            $adapter
        );
    }
}
