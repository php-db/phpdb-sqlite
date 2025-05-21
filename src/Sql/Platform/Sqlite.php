<?php

namespace Laminas\Db\Sqlite\Sql\Platform;

use Laminas\Db\Sql\Ddl\AlterTable;
use Laminas\Db\Sql\Ddl\CreateTable;
use Laminas\Db\Sql\Platform\AbstractPlatform;
use Laminas\Db\Sql\Select;

class Sqlite extends AbstractPlatform
{
    public function __construct()
    {
        $this->setTypeDecorator(Select::class, new SelectDecorator());
        $this->setTypeDecorator(CreateTable::class, new Ddl\CreateTableDecorator());
        $this->setTypeDecorator(AlterTable::class, new Ddl\AlterTableDecorator());
    }
}
