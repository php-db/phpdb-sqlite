<?php

declare(strict_types=1);

namespace PhpDb\Adapter\Sqlite\Container;

use PhpDb\Adapter\AdapterInterface;
use PhpDb\Adapter\SchemaAwareInterface;
use PhpDb\Adapter\Sqlite\Metadata\Source\SqliteMetadata;
use PhpDb\Metadata\MetadataInterface;
use Psr\Container\ContainerInterface;

final class MetadataInterfaceFactory
{
    public function __invoke(ContainerInterface $container): MetadataInterface&SqliteMetadata
    {
        /** @var AdapterInterface&SchemaAwareInterface $adapter */
        $adapter = $container->get(AdapterInterface::class);

        return new SqliteMetadata(
            $adapter
        );
    }
}
