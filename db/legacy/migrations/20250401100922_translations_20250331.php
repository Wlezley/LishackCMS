<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class Translations20250331 extends AbstractMigration
{
    public function up(): void
    {
        $this->execute("
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('admin.article.preview', 'cz', 'Náhled článku');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('admin.article.preview', 'en', 'Article preview');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('article.title.preview', 'cz', '(náhled)');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('article.title.preview', 'en', '(preview)');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('article.id.not-found', 'cz', 'Článek ID: %d nebyl nalezen');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('article.id.not-found', 'en', 'Article ID: %d not found');
        ");
    }

    public function down(): void
    {
    }
}
