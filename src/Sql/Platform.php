<?php

declare(strict_types=1);

namespace PhpDb\Sqlite\Sql;

use PhpDb\Sql\Platform\AbstractPlatform;
use PhpDb\Sql\Select;

final class Platform extends AbstractPlatform
{
    public function __construct()
    {
        $this->setTypeDecorator(Select::class, new SelectDecorator());
    }
}
