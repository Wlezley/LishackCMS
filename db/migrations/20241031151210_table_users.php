<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class TableUsers extends AbstractMigration
{
    public function up(): void
    {
        static $sql = <<< SQL
        CREATE TABLE IF NOT EXISTS `users` (
            `id` INT NOT NULL AUTO_INCREMENT,
            `name` VARCHAR(50) NOT NULL COLLATE utf8mb4_general_ci,
            `password` VARCHAR(100) NOT NULL COLLATE utf8mb4_general_ci,
            `email` VARCHAR(255) NULL DEFAULT NULL COLLATE utf8mb4_general_ci,
            `role` VARCHAR(50) NULL DEFAULT NULL COLLATE utf8mb4_general_ci,
            `full_name` VARCHAR(150) NULL DEFAULT NULL COLLATE utf8mb4_general_ci,
            `session_id` VARCHAR(150) NULL DEFAULT NULL COLLATE utf8mb4_general_ci,
            `deleted` TINYINT NOT NULL DEFAULT 0,
            `enabled` TINYINT NOT NULL DEFAULT 1,
            PRIMARY KEY (`id`) USING BTREE
        )
        COLLATE=utf8mb4_general_ci
        ENGINE=InnoDB
        AUTO_INCREMENT=1;
        SQL;
        $this->execute($sql);
    }

    public function down(): void
    {
    }
}
