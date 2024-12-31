<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class SecurityPasswords extends AbstractMigration
{
    static $sql = <<< SQL
    SQL;
    public function up(): void
    {
        $sql = <<< SQL
        ALTER TABLE `users`
            CHANGE COLUMN `password` `password` VARCHAR(255) NOT NULL COLLATE utf8mb4_general_ci AFTER `name`;
        SQL;
        $this->execute($sql);
    }

    public function down(): void
    {
        static $sql = <<< SQL
        ALTER TABLE `users`
            CHANGE COLUMN `password` `password` VARCHAR(100) NOT NULL COLLATE utf8mb4_general_ci AFTER `name`;
        SQL;
        $this->execute($sql);
    }
}
