<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CmsConfigCategory extends AbstractMigration
{
    public function up(): void
    {
        $this->execute("
            ALTER TABLE `cms_config`
                ADD COLUMN `category` VARCHAR(50) NULL DEFAULT NULL AFTER `name`;
        ");
    }

    public function down(): void
    {
    }
}
