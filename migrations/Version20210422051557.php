<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210422051557 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Initial migration';
    }

    public function up(Schema $schema) : void
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() !== 'postgresql',
            'Migration can only be executed safely on \'postgresql\'.'
        );

        $this->addSql('CREATE SCHEMA main;');
        $this->addSql('SET search_path = main;');
        $this->addSql('
          CREATE TABLE "product" (
            "id" int4 NOT NULL,
            "name" varchar(255) NOT NULL,
            PRIMARY KEY ("id")
          );
        ');
    }

    public function down(Schema $schema) : void
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() !== 'postgresql',
            'Migration can only be executed safely on \'postgresql\'.'
        );

        $this->addSql('DROP SCHEMA main CASCADE;');
    }
}
