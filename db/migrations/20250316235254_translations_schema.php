<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class TranslationsSchema extends AbstractMigration
{
    public function up(): void
    {
        $this->execute("
            CREATE TABLE `translations` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `key` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci',
                `lang` VARCHAR(3) NOT NULL COLLATE 'utf8mb4_general_ci',
                `text` TEXT NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
                PRIMARY KEY (`id`) USING BTREE,
                UNIQUE INDEX `key_lang` (`key`, `lang`) USING BTREE
            )
            COLLATE='utf8mb4_general_ci'
            ENGINE=InnoDB;
        ");
    }

    public function down(): void
    {
        // $this->execute("DROP TABLE `translation`;");
    }
}
