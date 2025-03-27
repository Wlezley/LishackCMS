<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class Translations20250326 extends AbstractMigration
{
    public function up(): void
    {
        $this->execute("
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('modal.body.delete-article', 'cz', 'Opravdu chcete článek <strong>%s</strong> smazat?');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('modal.body.delete-article', 'en', 'Are you sure you want to delete the article <strong>%s</strong>?');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('form.article.save-copy', 'cz', 'Vytvořit kopii');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('form.article.save-copy', 'en', 'Create copy');
        ");
    }

    public function down(): void
    {
    }
}
