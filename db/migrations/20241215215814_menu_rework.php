<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class MenuRework extends AbstractMigration
{
    public function up(): void
    {
        $this->execute("
            ALTER TABLE `menu`
                DROP COLUMN `lft`,
                DROP COLUMN `rgt`,
                DROP COLUMN `depth`,
                ADD COLUMN `position` INT(11) NULL DEFAULT NULL AFTER `parent_id`;
        ");
    }

    public function down(): void
    {
        $this->execute("
            ALTER TABLE `menu`
                ADD COLUMN `lft` INT(11) NULL DEFAULT NULL AFTER `parent_id`,
                ADD COLUMN `rgt` INT(11) NULL DEFAULT NULL AFTER `lft`,
                ADD COLUMN `depth` INT(11) NULL DEFAULT NULL AFTER `rgt`,
                DROP COLUMN `position`;
        ");
    }
}
