<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class LangSchema extends AbstractMigration
{
    public function up(): void
    {
        $this->execute("
            CREATE TABLE `lang` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `lang` VARCHAR(3) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
                `name` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
                `enabled` ENUM('1','0') NOT NULL DEFAULT '1' COLLATE 'utf8mb4_general_ci',
                `default` ENUM('1','0') NOT NULL DEFAULT '0' COLLATE 'utf8mb4_general_ci',
                PRIMARY KEY (`id`) USING BTREE,
                UNIQUE INDEX `lang` (`lang`) USING BTREE
            )
            COLLATE='utf8mb4_general_ci'
            ENGINE=InnoDB;

            INSERT INTO `lang` (`id`, `lang`, `name`, `enabled`, `default`) VALUES (1, 'cs', 'Čeština', '1', '1');
            INSERT INTO `lang` (`id`, `lang`, `name`, `enabled`, `default`) VALUES (2, 'en', 'English', '1', '0');
        ");
    }

    public function down(): void
    {
        // $this->execute("DROP TABLE `lang`;");
    }
}
