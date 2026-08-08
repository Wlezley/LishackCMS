<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class TableUsers extends AbstractMigration
{
    public function up(): void
    {
        $this->execute("
            CREATE TABLE IF NOT EXISTS `users` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `name` VARCHAR(50) NOT NULL COLLATE 'utf8mb4_general_ci',
                `password` VARCHAR(100) NOT NULL COLLATE 'utf8mb4_general_ci',
                `email` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
                `role` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
                `full_name` VARCHAR(150) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
                `session_id` VARCHAR(150) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
                `deleted` TINYINT(4) NULL DEFAULT '0',
                `enabled` TINYINT(4) NULL DEFAULT '1',
                PRIMARY KEY (`id`) USING BTREE
            )
            COLLATE='utf8mb4_general_ci'
            ENGINE=InnoDB
            AUTO_INCREMENT=1;
        ");
    }

    public function down(): void
    {
    }
}
