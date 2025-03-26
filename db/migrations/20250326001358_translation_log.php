<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class TranslationLog extends AbstractMigration
{
    public function up(): void
    {
        $this->execute("
            CREATE TABLE `translations_log` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `date` DATETIME NOT NULL DEFAULT current_timestamp(),
                `key` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci',
                `lang` VARCHAR(3) NOT NULL COLLATE 'utf8mb4_general_ci',
                `type` ENUM('key','arg','unk') NOT NULL DEFAULT 'unk' COLLATE 'utf8mb4_general_ci',
                `message` TEXT NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
                PRIMARY KEY (`id`) USING BTREE
            )
            COLLATE='utf8mb4_general_ci'
            ENGINE=InnoDB
            ROW_FORMAT=DYNAMIC;
        ");
    }

    public function down(): void
    {
    }
}
