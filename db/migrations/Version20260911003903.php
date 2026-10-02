<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260911003903 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rename column default to default_value in dataset_column table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE dataset_column CHANGE `default` default_value LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE dataset_column CHANGE default_value `default` LONGTEXT DEFAULT NULL');
    }
}
