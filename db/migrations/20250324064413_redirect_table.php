<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class RedirectTable extends AbstractMigration
{
    public function up(): void
    {
        $this->execute("
            CREATE TABLE `redirect` (
                `source` VARCHAR(300) NOT NULL COLLATE 'utf8mb4_general_ci',
                `target` VARCHAR(300) NOT NULL COLLATE 'utf8mb4_general_ci',
                `code` INT(11) NULL DEFAULT '302',
                `enabled` TINYINT(4) NULL DEFAULT '1',
                UNIQUE INDEX `source` (`source`) USING BTREE
            )
            COLLATE='utf8mb4_general_ci'
            ENGINE=InnoDB;
        ");
    }

    public function down(): void
    {
        // $this->execute("DROP TABLE `redirect`;");
    }
}
