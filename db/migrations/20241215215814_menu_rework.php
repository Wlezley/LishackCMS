<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class MenuRework extends AbstractMigration
{
    public function up(): void
    {
        static $sql = <<< SQL
        ALTER TABLE `menu`
            DROP COLUMN `lft`,
            DROP COLUMN `rgt`,
            DROP COLUMN `depth`,
            ADD COLUMN `position` INT NULL DEFAULT NULL AFTER `parent_id`;
        SQL;
        $this->execute($sql);
    }

    public function down(): void
    {
        static $sql = <<< SQL
        ALTER TABLE `menu`
            ADD COLUMN `lft` INT NULL DEFAULT NULL AFTER `parent_id`,
            ADD COLUMN `rgt` INT NULL DEFAULT NULL AFTER `lft`,
            ADD COLUMN `depth` INT NULL DEFAULT NULL AFTER `rgt`,
            DROP COLUMN `position`;
        SQL;
        $this->execute($sql);
    }
}
