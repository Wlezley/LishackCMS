<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class MenuSystem extends AbstractMigration
{
    public function up(): void
    {
        $this->execute("
            CREATE TABLE `menu` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `parent_id` INT(11) NULL DEFAULT NULL,
                `lft` INT(11) NULL DEFAULT NULL,
                `rgt` INT(11) NULL DEFAULT NULL,
                `depth` INT(11) NULL DEFAULT NULL,
                `name` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
                `name_url` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
                `title` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
                `description` TEXT NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
                `body` TEXT NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
                `hidden` ENUM('0','1') NOT NULL DEFAULT '0' COLLATE 'utf8mb4_general_ci',
                PRIMARY KEY (`id`) USING BTREE
            )
            COLLATE='utf8mb4_general_ci'
            ENGINE=InnoDB;

            INSERT INTO `menu` (`id`, `parent_id`, `lft`, `rgt`, `depth`, `name`, `name_url`, `title`, `description`, `body`, `hidden`) VALUES
                (1, 0, 1, 2, 0, NULL, NULL, 'MAIN_MENU', NULL, NULL, '0');
        ");
    }

    public function down(): void
    {
    }
}
