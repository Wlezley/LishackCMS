<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260820221450 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Limit CMS config category length to 6, using CmsConfigCategoryEnum';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE cms_config CHANGE category category VARCHAR(6) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE cms_config CHANGE category category VARCHAR(255) NOT NULL');
    }
}
