<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class Article extends AbstractMigration
{
    public function up(): void
    {
        $this->execute("
            CREATE TABLE `article` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `title` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
                `name_url` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
                `content` TEXT NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
                `published` TINYINT(1) NULL DEFAULT NULL,
                `published_at` DATETIME NULL DEFAULT NULL,
                `updated_at` DATETIME NULL DEFAULT NULL,
                `user_id` INT(11) NULL DEFAULT NULL,
                `robots` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
                `canonical_url` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
                `og_title` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
                `og_description` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
                `og_image` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
                `og_url` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
                `og_type` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
                `meta_title` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
                `meta_description` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
                PRIMARY KEY (`id`) USING BTREE
            )
            COLLATE='utf8mb4_general_ci'
            ENGINE=InnoDB;

            CREATE TABLE `article_category` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `article_id` INT(11) NULL DEFAULT NULL,
                `menu_id` INT(11) NULL DEFAULT NULL,
                `order` INT(11) NULL DEFAULT '1',
                PRIMARY KEY (`id`) USING BTREE
            )
            COLLATE='utf8mb4_general_ci'
            ENGINE=InnoDB;
        ");
    }

    public function down(): void
    {
        // $this->execute("DROP TABLE `article`;");
        // $this->execute("DROP TABLE `article_category`;");
    }
}
