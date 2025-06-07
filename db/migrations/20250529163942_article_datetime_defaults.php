<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class ArticleDatetimeDefaults extends AbstractMigration
{
    public function up(): void
    {
        $this->execute("ALTER TABLE `article`
            CHANGE COLUMN `published_at` `published_at` DATETIME NULL DEFAULT current_timestamp() AFTER `published`,
            CHANGE COLUMN `updated_at` `updated_at` DATETIME NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() AFTER `published_at`;
        ");
    }

    public function down(): void
    {
        $this->execute("ALTER TABLE `article`
            CHANGE COLUMN `published_at` `published_at` DATETIME NULL DEFAULT NULL AFTER `published`,
            CHANGE COLUMN `updated_at` `updated_at` DATETIME NULL DEFAULT NULL AFTER `published_at`;
        ");
    }
}
