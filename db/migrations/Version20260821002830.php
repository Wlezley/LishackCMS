<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260821002830 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add creation timestamps and make update timestamp nullable';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE article ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, CHANGE updated_at updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE log_sms ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, DROP date');
        $this->addSql('ALTER TABLE translations_log ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, DROP date');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE article DROP created_at, CHANGE updated_at updated_at DATETIME DEFAULT CURRENT_TIMESTAMP');
        $this->addSql('ALTER TABLE log_sms ADD date DATETIME DEFAULT CURRENT_TIMESTAMP, DROP created_at');
        $this->addSql('ALTER TABLE translations_log ADD date DATETIME DEFAULT CURRENT_TIMESTAMP, DROP created_at');
    }
}
