<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261017000951 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds language_code to the article table; adds meta_keywords to the seo table';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE article ADD language_code VARCHAR(2) DEFAULT NULL');
        $this->addSql('ALTER TABLE article ADD CONSTRAINT FK_23A0E66451CDAD4 FOREIGN KEY (language_code) REFERENCES language (code) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_23A0E66451CDAD4 ON article (language_code)');
        $this->addSql('ALTER TABLE seo ADD meta_keywords VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE article DROP FOREIGN KEY FK_23A0E66451CDAD4');
        $this->addSql('DROP INDEX IDX_23A0E66451CDAD4 ON article');
        $this->addSql('ALTER TABLE article DROP language_code');
        $this->addSql('ALTER TABLE seo DROP meta_keywords');
    }
}
