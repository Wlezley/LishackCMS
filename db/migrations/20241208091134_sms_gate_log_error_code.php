<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class SmsGateLogErrorCode extends AbstractMigration
{
    public function up(): void
    {
        static $sql = <<< SQL
        ALTER TABLE `log_sms`
            ADD COLUMN `error_code` TINYINT NULL DEFAULT NULL AFTER `message`,
            DROP COLUMN `response`;
        SQL;
        $this->execute($sql);
    }

    public function down(): void
    {
        static $sql = <<< SQL
        ALTER TABLE `log_sms`
            ADD COLUMN `response` TEXT NULL DEFAULT NULL AFTER `message`,
            DROP COLUMN `error_code`;
        SQL;
        $this->execute($sql);
    }
}
