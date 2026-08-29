<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260829032605 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update defaults and nullability of article, translation log, and user columns';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE article CHANGE published published TINYINT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE translations_log CHANGE message message LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE users CHANGE password password VARCHAR(255) DEFAULT NULL, CHANGE role role VARCHAR(8) DEFAULT \'guest\' NOT NULL, CHANGE session_id session_id VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE article CHANGE published published TINYINT DEFAULT NULL');
        $this->addSql('ALTER TABLE translations_log CHANGE message message LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE users CHANGE password password VARCHAR(255) NOT NULL, CHANGE role role VARCHAR(8) NOT NULL, CHANGE session_id session_id VARCHAR(255) NOT NULL');
    }
}
