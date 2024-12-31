<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class UsersLastLogin extends AbstractMigration
{
    public function up(): void
    {
        static $sql = <<< SQL
        ALTER TABLE `users`
            ADD COLUMN `created` DATETIME NULL DEFAULT NOW() AFTER `enabled`,
            ADD COLUMN `last_login` DATETIME NULL DEFAULT NULL AFTER `created`,
            ADD UNIQUE INDEX `name` (`name`);
        SQL;
        $this->execute($sql);
    }

    public function down(): void
    {
        static $sql = <<< SQL
        ALTER TABLE `users`
            DROP COLUMN `created`,
            DROP COLUMN `last_login`,
            DROP INDEX `name`;
        SQL;
        $this->execute($sql);
    }
}
