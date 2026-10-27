<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261027051146 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update dataset_column indexes; add unique index to translations table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE dataset_column RENAME INDEX dataset_column_dataset_id_slug_unique TO dataset_id_slug_unique');
        $this->addSql('ALTER TABLE dataset_column RENAME INDEX dataset_column_dataset_id_column_id_unique TO dataset_id_column_id_unique');
        $this->addSql('CREATE UNIQUE INDEX translation_key_language_unique ON translations (translation_key, language)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE dataset_column RENAME INDEX dataset_id_column_id_unique TO dataset_column_dataset_id_column_id_unique');
        $this->addSql('ALTER TABLE dataset_column RENAME INDEX dataset_id_slug_unique TO dataset_column_dataset_id_slug_unique');
        $this->addSql('DROP INDEX translation_key_language_unique ON translations');
    }
}
