<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class ArticleSystemRework extends AbstractMigration
{
    public function up(): void
    {
        // ARTICLE
        $this->execute("RENAME TABLE `article` TO `_article_old`;"); // CREATE BACKUP
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
        ");

        // ARTICLE_CATEGORY
        $this->execute("RENAME TABLE `article_category` TO `_article_category_old`;"); // CREATE BACKUP
        $this->execute("
            CREATE TABLE `article_category` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `article_id` INT(11) NULL DEFAULT NULL,
                `article_name_url` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
                `category_id` INT(11) NULL DEFAULT NULL,
                PRIMARY KEY (`id`) USING BTREE
            )
            COLLATE='utf8mb4_general_ci'
            ENGINE=InnoDB;
        ");

        // CATEGORY (formerly called MENU)
        $this->execute("RENAME TABLE `menu` TO `_menu_old`;"); // CREATE BACKUP
        $this->execute("
            CREATE TABLE `category` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `parent_id` INT(11) NULL DEFAULT NULL,
                `position` INT(11) NULL DEFAULT NULL,
                `name` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
                `name_url` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
                `title` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
                `description` TEXT NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
                `body` TEXT NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
                `hidden` ENUM('0','1') NOT NULL DEFAULT '0' COLLATE 'utf8mb4_general_ci',
                PRIMARY KEY (`id`) USING BTREE
            )
            COLLATE='utf8mb4_general_ci'
            ENGINE=InnoDB
            ROW_FORMAT=DYNAMIC;
        ");


        // ARTICLE EXAMPLE DATA
        $this->execute("
            INSERT INTO `article` (`id`, `title`, `name_url`, `content`, `published`, `published_at`, `updated_at`, `user_id`, `robots`, `canonical_url`, `og_title`, `og_description`, `og_image`, `og_url`, `og_type`, `meta_title`, `meta_description`) VALUES (1, 'Hlavní stránka', 'home', '<h2>Main page test</h2>', 1, '2025-03-26 00:00:00', '2025-03-26 00:00:00', 1, 'index, follow', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
            INSERT INTO `article` (`id`, `title`, `name_url`, `content`, `published`, `published_at`, `updated_at`, `user_id`, `robots`, `canonical_url`, `og_title`, `og_description`, `og_image`, `og_url`, `og_type`, `meta_title`, `meta_description`) VALUES (2, 'Test', 'ddd', '<h1>Page DDD</h1>\r\n\r\n<p>TEST!</p>\r\n\r\n<div class=\"bg-dark\">DARK TEST</div>\r\n', 1, '2025-03-26 00:00:00', '2025-03-26 00:00:00', 1, 'index, follow', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
        ");

        // ARTICLE_CATEGORY EXAMPLE DATA
        $this->execute("
            INSERT INTO `article_category` (`id`, `article_id`, `article_name_url`, `category_id`) VALUES (1, 1, 'home', 1);
            INSERT INTO `article_category` (`id`, `article_id`, `article_name_url`, `category_id`) VALUES (2, 2, 'ddd', 11);
            INSERT INTO `article_category` (`id`, `article_id`, `article_name_url`, `category_id`) VALUES (3, 2, 'ddd', 1);
        ");

        // CATEGORY EXAMPLE DATA
        $this->execute("
            INSERT INTO `category` (`id`, `parent_id`, `position`, `name`, `name_url`, `title`, `description`, `body`, `hidden`) VALUES (1, 0, 0, 'Hlavní stránka', NULL, 'MAIN_MENU', NULL, NULL, '0');
            INSERT INTO `category` (`id`, `parent_id`, `position`, `name`, `name_url`, `title`, `description`, `body`, `hidden`) VALUES (2, 1, 1, 'aaa', 'aaa', '', NULL, NULL, '0');
            INSERT INTO `category` (`id`, `parent_id`, `position`, `name`, `name_url`, `title`, `description`, `body`, `hidden`) VALUES (3, 1, 2, 'aaa2', 'aaa2', '', NULL, NULL, '0');
            INSERT INTO `category` (`id`, `parent_id`, `position`, `name`, `name_url`, `title`, `description`, `body`, `hidden`) VALUES (4, 1, 6, 'aaa3', 'aaa3', '', NULL, NULL, '0');
            INSERT INTO `category` (`id`, `parent_id`, `position`, `name`, `name_url`, `title`, `description`, `body`, `hidden`) VALUES (5, 2, 3, 'bbb', 'bbb', '', NULL, NULL, '0');
            INSERT INTO `category` (`id`, `parent_id`, `position`, `name`, `name_url`, `title`, `description`, `body`, `hidden`) VALUES (6, 2, 4, 'bbb2', 'bbb2', '', NULL, NULL, '0');
            INSERT INTO `category` (`id`, `parent_id`, `position`, `name`, `name_url`, `title`, `description`, `body`, `hidden`) VALUES (7, 2, 5, 'bbb3', 'bbb3', '', NULL, NULL, '0');
            INSERT INTO `category` (`id`, `parent_id`, `position`, `name`, `name_url`, `title`, `description`, `body`, `hidden`) VALUES (11, 5, 7, 'ccc', 'ccc', '', NULL, NULL, '0');
        ");
    }

    public function down(): void
    {
        // $this->execute("DROP TABLE `article`;");
        // $this->execute("RENAME TABLE `_article_old` TO `article`;");

        // $this->execute("DROP TABLE `article_category`;");
        // $this->execute("RENAME TABLE `_article_category_old` TO `article_category`;");

        // $this->execute("DROP TABLE `category`;");
        // $this->execute("RENAME TABLE `_menu_old` TO `menu`;");
    }
}
