<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class TableCmsConfig extends AbstractMigration
{
    public function up(): void
    {
        static $sql = <<< SQL
        CREATE TABLE `cms_config` (
            `name` VARCHAR(50) NULL DEFAULT NULL COLLATE utf8mb4_general_ci,
            `value` TEXT NULL DEFAULT NULL COLLATE utf8mb4_general_ci,
            UNIQUE INDEX `name` (`name`) USING BTREE
        )
        COLLATE=utf8mb4_general_ci
        ENGINE=InnoDB
        SQL;
        $this->execute($sql);
    }

    public function down(): void
    {
    }
}
