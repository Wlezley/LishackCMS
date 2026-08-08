<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CmsConfigRenameColumns20250321 extends AbstractMigration
{
    public function up(): void
    {
        $this->execute("
            ALTER TABLE `cms_config`
                CHANGE COLUMN `name` `key` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci' FIRST,
                DROP INDEX `name`,
                ADD UNIQUE INDEX `name` (`key`) USING BTREE;
        ");
    }

    public function down(): void
    {
        $this->execute("
            ALTER TABLE `cms_config`
                CHANGE COLUMN `key` `name` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci' FIRST,
                DROP INDEX `key`,
                ADD UNIQUE INDEX `key` (`name`) USING BTREE;
        ");
    }
}
