<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260820221452 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Insert default languages';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            <<<'SQL'
                INSERT INTO language (
                    id,
                    language_code,
                    name,
                    html_lang,
                    locale,
                    enabled,
                    `default`
                )
                VALUES
                    (1, 'cz', 'Čeština', 'cs', 'cs_CZ', 1, 1),
                    (2, 'en', 'English', 'en', 'en_US', 1, 0)
                SQL
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql(
            <<<'SQL'
                DELETE FROM language
                WHERE id IN (1, 2)
                SQL
        );
    }
}
