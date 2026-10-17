<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261017015022 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add seo_id to category table';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE category ADD seo_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE category ADD CONSTRAINT FK_64C19C197E3DD86 FOREIGN KEY (seo_id) REFERENCES seo (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_64C19C197E3DD86 ON category (seo_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE category DROP FOREIGN KEY FK_64C19C197E3DD86');
        $this->addSql('DROP INDEX IDX_64C19C197E3DD86 ON category');
        $this->addSql('ALTER TABLE category DROP seo_id');
    }
}
