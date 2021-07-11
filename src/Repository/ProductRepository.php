<?php

declare(strict_types=1);

namespace TmpApp\Repository;

class ProductRepository extends Repository
{
    protected function getTableName(): string
    {
        return 'main.product';
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function getNameById(int $id): string
    {
        return (string) $this->connection->fetchOne(
            "SELECT name FROM {$this->getTableName()} WHERE id = :id",
            ['id' => $id,],
        );
    }
}