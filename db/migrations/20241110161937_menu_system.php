<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class MenuSystem extends AbstractMigration
{
    public function up(): void
    {
        static $sql = <<< SQL
        CREATE TABLE `menu` (
            `id` INT NOT NULL AUTO_INCREMENT,
            `parent_id` INT NULL DEFAULT NULL,
            `lft` INT NULL DEFAULT NULL,
            `rgt` INT NULL DEFAULT NULL,
            `depth` INT NULL DEFAULT NULL,
            `name` VARCHAR(255) NULL DEFAULT NULL COLLATE utf8mb4_general_ci,
            `name_url` VARCHAR(255) NULL DEFAULT NULL COLLATE utf8mb4_general_ci,
            `title` VARCHAR(255) NULL DEFAULT NULL COLLATE utf8mb4_general_ci,
            `description` TEXT NULL DEFAULT NULL COLLATE utf8mb4_general_ci,
            `body` TEXT NULL DEFAULT NULL COLLATE utf8mb4_general_ci,
            `hidden` ENUM('0', '1') NOT NULL DEFAULT '0' COLLATE utf8mb4_general_ci,
            PRIMARY KEY (`id`) USING BTREE
        )
        COLLATE=utf8mb4_general_ci
        ENGINE=InnoDB;

        INSERT INTO `menu`
            (`id`, `parent_id`, `lft`, `rgt`, `depth`, `hidden`, `title`, `name`, `name_url`, `description`, `body`)
        VALUES
            (1, 0, 1, 2, 0, '0', 'MAIN_MENU', NULL, NULL, NULL, NULL);
        SQL;
        $this->execute($sql);
    }

    public function down(): void
    {
    }
}
