<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class ArticleSystemUpdate20250401 extends AbstractMigration
{
    public function up(): void
    {
        $this->execute("
            ALTER TABLE `article`
                CHANGE COLUMN `name_url` `name_url` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci' AFTER `id`,
                ADD UNIQUE INDEX `name_url` (`name_url`);

            ALTER TABLE `article`
                ADD COLUMN `category_id` INT(11) NULL DEFAULT '1' AFTER `name_url`;

            RENAME TABLE `article_category` TO `_article_category_old`;
        ");
    }

    public function down(): void
    {
        $this->execute("
            RENAME TABLE `_article_category_old` TO `article_category`;

            ALTER TABLE `article`
                DROP COLUMN `category_id`;

            ALTER TABLE `article`
                CHANGE COLUMN `name_url` `name_url` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci' AFTER `title`,
                DROP INDEX `name_url`;
        ");
    }
}
