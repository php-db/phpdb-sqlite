<?php

namespace PhpDb\Adapter\Sqlite\Sql\Platform;

use PhpDb\Sql\Ddl\AlterTable;
use PhpDb\Sql\Ddl\CreateTable;
use PhpDb\Sql\Platform\AbstractPlatform;
use PhpDb\Sql\Select;

final class Sqlite extends AbstractPlatform
{
    public function __construct()
    {
        $this->setTypeDecorator(Select::class, new SelectDecorator());
        $this->setTypeDecorator(CreateTable::class, new Ddl\CreateTableDecorator());
        $this->setTypeDecorator(AlterTable::class, new Ddl\AlterTableDecorator());
    }
}
