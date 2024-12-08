<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class UsersLastLogin extends AbstractMigration
{
    public function up(): void
    {
        $this->execute("
            ALTER TABLE `users`
                ADD COLUMN `created` DATETIME NULL DEFAULT NOW() AFTER `enabled`,
                ADD COLUMN `last_login` DATETIME NULL DEFAULT NULL AFTER `created`,
                ADD UNIQUE INDEX `name` (`name`);
        ");
    }

    public function down(): void
    {
        $this->execute("
            ALTER TABLE `users`
                DROP COLUMN `created`,
                DROP COLUMN `last_login`,
                DROP INDEX `name`;
        ");
    }
}
