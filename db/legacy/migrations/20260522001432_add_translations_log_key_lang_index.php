<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddTranslationsLogKeyLangIndex extends AbstractMigration
{
    public function up(): void
    {
        $this->execute(<<<MARIADB
            CREATE INDEX key_lang
            ON translations_log (`key`, lang)
        MARIADB);
    }

    public function down(): void
    {
        $this->execute(<<<MARIADB
            DROP INDEX key_lang
            ON translations_log
        MARIADB);
    }
}
