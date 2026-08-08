<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class DatasetSystem20250421 extends AbstractMigration
{
    public function up(): void
    {
        $this->execute("CREATE TABLE `dataset` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `name` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
                `slug` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
                `component` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
                `presenter` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
                `active` TINYINT(1) NULL DEFAULT NULL,
                PRIMARY KEY (`id`) USING BTREE
            )
            COLLATE='utf8mb4_general_ci'
            ENGINE=InnoDB;");

        $this->execute("CREATE TABLE `dataset_column` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `dataset_id` INT(11) NULL DEFAULT NULL,
                `column_id` INT(11) NULL DEFAULT NULL,
                `name` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
                `slug` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
                `type` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
                `required` TINYINT(1) NULL DEFAULT '0',
                `deleted` TINYINT(1) NULL DEFAULT '0',
                PRIMARY KEY (`id`) USING BTREE,
                UNIQUE INDEX `object_id_column_id` (`dataset_id`, `column_id`) USING BTREE
            )
            COLLATE='utf8mb4_general_ci'
            ENGINE=InnoDB;");
    }

    public function down(): void
    {
        $this->execute("DROP TABLE `dataset`");
        $this->execute("DROP TABLE `dataset_column`");
    }
}
