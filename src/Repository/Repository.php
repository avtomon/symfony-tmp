<?php

declare(strict_types=1);

namespace TmpApp\Repository;

use Doctrine\DBAL\Connection;

abstract class Repository
{
    protected Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    abstract protected function getTableName(): string;
}
