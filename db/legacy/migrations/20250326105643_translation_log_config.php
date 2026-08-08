<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class TranslationLogConfig extends AbstractMigration
{
    public function up(): void
    {
        $this->execute("
            INSERT INTO `cms_config` (`key`, `category`, `value`) VALUES ('LOG_TRANSLATION_FALLBACK', 'SYS', '1');
        ");
    }

    public function down(): void
    {
    }
}
