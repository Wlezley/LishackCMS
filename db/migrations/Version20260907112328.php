<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260907112328 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update language table structure and translations table foreign key';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_D4DB71B5451CDAD4 ON language');
        $this->addSql('ALTER TABLE language MODIFY id INT NOT NULL');
        $this->addSql('ALTER TABLE language DROP id, CHANGE language_code code VARCHAR(2) NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (code)');
        $this->addSql('ALTER TABLE translations CHANGE lang language VARCHAR(2) NOT NULL');
        $this->addSql('ALTER TABLE translations ADD CONSTRAINT FK_C6B7DA87D4DB71B5 FOREIGN KEY (language) REFERENCES language (code) ON DELETE RESTRICT');
        $this->addSql('CREATE INDEX IDX_C6B7DA87D4DB71B5 ON translations (language)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE language ADD id INT AUTO_INCREMENT NOT NULL, CHANGE code language_code VARCHAR(2) NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D4DB71B5451CDAD4 ON language (language_code)');
        $this->addSql('ALTER TABLE translations DROP FOREIGN KEY FK_C6B7DA87D4DB71B5');
        $this->addSql('DROP INDEX IDX_C6B7DA87D4DB71B5 ON translations');
        $this->addSql('ALTER TABLE translations CHANGE language lang VARCHAR(2) NOT NULL');
    }
}
