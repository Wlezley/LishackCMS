<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261027040720 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update dataset_column table structure and foreign key';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE dataset CHANGE slug slug VARCHAR(50) DEFAULT NULL, CHANGE component component VARCHAR(50) DEFAULT NULL, CHANGE presenter presenter VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE dataset_column DROP INDEX UNIQ_1B2553C4D47C2D1B, ADD INDEX IDX_1B2553C4D47C2D1B (dataset_id)');
        $this->addSql('DROP INDEX UNIQ_1B2553C4989D9B62 ON dataset_column');
        $this->addSql('DROP INDEX UNIQ_1B2553C4BE8E8ED5 ON dataset_column');
        $this->addSql('CREATE UNIQUE INDEX dataset_column_dataset_id_slug_unique ON dataset_column (dataset_id, slug)');
        $this->addSql('CREATE UNIQUE INDEX dataset_column_dataset_id_column_id_unique ON dataset_column (dataset_id, column_id)');
        $this->addSql('ALTER TABLE dataset_column ADD CONSTRAINT FK_1B2553C4D47C2D1B FOREIGN KEY (dataset_id) REFERENCES dataset (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE dataset_column DROP FOREIGN KEY FK_1B2553C4D47C2D1B');
        $this->addSql('DROP INDEX dataset_column_dataset_id_slug_unique ON dataset_column');
        $this->addSql('DROP INDEX dataset_column_dataset_id_column_id_unique ON dataset_column');
        $this->addSql('ALTER TABLE dataset_column DROP INDEX IDX_1B2553C4D47C2D1B, ADD UNIQUE INDEX UNIQ_1B2553C4D47C2D1B (dataset_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1B2553C4989D9B62 ON dataset_column (slug)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1B2553C4BE8E8ED5 ON dataset_column (column_id)');
        $this->addSql('ALTER TABLE dataset CHANGE slug slug VARCHAR(50) NOT NULL, CHANGE component component VARCHAR(50) NOT NULL, CHANGE presenter presenter VARCHAR(50) NOT NULL');
    }
}
