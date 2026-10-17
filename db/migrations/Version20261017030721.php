<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261017030721 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Fix dataset_column unique indexes';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_1B2553C4BE8E8ED5 ON dataset_column');
        $this->addSql('DROP INDEX UNIQ_1B2553C4989D9B62 ON dataset_column');
        $this->addSql('CREATE UNIQUE INDEX dataset_column_dataset_column_id_unique ON dataset_column (dataset, column_id)');
        $this->addSql('CREATE UNIQUE INDEX dataset_column_dataset_slug_unique ON dataset_column (dataset, slug)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX dataset_column_dataset_column_id_unique ON dataset_column');
        $this->addSql('DROP INDEX dataset_column_dataset_slug_unique ON dataset_column');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1B2553C4BE8E8ED5 ON dataset_column (column_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1B2553C4989D9B62 ON dataset_column (slug)');
    }
}
