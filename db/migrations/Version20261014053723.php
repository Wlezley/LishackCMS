<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261014053723 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create SEO table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE seo (robots VARCHAR(255) DEFAULT NULL, canonical_url VARCHAR(255) DEFAULT NULL, og_title VARCHAR(255) DEFAULT NULL, og_description VARCHAR(255) DEFAULT NULL, og_image VARCHAR(255) DEFAULT NULL, og_url VARCHAR(255) DEFAULT NULL, og_type VARCHAR(255) DEFAULT NULL, meta_title VARCHAR(255) DEFAULT NULL, meta_description VARCHAR(255) DEFAULT NULL, id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE seo');
    }
}
