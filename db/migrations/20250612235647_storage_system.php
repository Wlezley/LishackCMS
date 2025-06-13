<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class StorageSystem extends AbstractMigration
{
    public function up(): void
    {
        $this->execute(
            "CREATE TABLE `storage_files` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `tree_id` INT(11) NOT NULL DEFAULT '0',
                `owner_id` INT(11) NOT NULL DEFAULT '0',
                `name` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci',
                `name_url` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci',
                `type` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci',
                `size` INT(11) NOT NULL DEFAULT '0',
                `checksum` VARCHAR(32) NOT NULL COLLATE 'utf8mb4_general_ci',
                `storage_id` VARCHAR(16) NOT NULL COLLATE 'utf8mb4_general_ci',
                `download_id` VARCHAR(16) NOT NULL COLLATE 'utf8mb4_general_ci',
                `uploaded_at` DATETIME NOT NULL DEFAULT current_timestamp(),
                `modified_at` DATETIME NULL DEFAULT NULL ON UPDATE current_timestamp(),
                `deleted_at` DATETIME NULL DEFAULT NULL,
                PRIMARY KEY (`id`) USING BTREE
            )
            COLLATE='utf8mb4_general_ci'
            ENGINE=InnoDB;"
        );

        $this->execute(
            "CREATE TABLE `storage_tree` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `parent_id` INT(11) NOT NULL DEFAULT '0',
                `owner_id` INT(11) NOT NULL DEFAULT '0',
                `name` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci',
                `name_url` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci',
                `created_at` DATETIME NOT NULL DEFAULT current_timestamp(),
                `modified_at` DATETIME NULL DEFAULT NULL ON UPDATE current_timestamp(),
                `deleted_at` DATETIME NULL DEFAULT NULL,
                PRIMARY KEY (`id`) USING BTREE
            )
            COLLATE='utf8mb4_general_ci'
            ENGINE=InnoDB;"
        );
    }

    public function down(): void
    {
        $this->execute("DROP TABLE `storage_files`;");
        $this->execute("DROP TABLE `storage_tree`;");
    }
}
