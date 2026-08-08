<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class SecurityPasswords extends AbstractMigration
{
    public function up(): void
    {
        $this->execute("
            ALTER TABLE `users`
                CHANGE COLUMN `password` `password` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci' AFTER `name`;
        ");
    }

    public function down(): void
    {
        $this->execute("
            ALTER TABLE `users`
                CHANGE COLUMN `password` `password` VARCHAR(100) NOT NULL COLLATE 'utf8mb4_general_ci' AFTER `name`;
        ");
    }
}
