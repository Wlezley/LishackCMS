<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class SmsGateLogErrorCode extends AbstractMigration
{
    public function up(): void
    {
        $this->execute("
            ALTER TABLE `log_sms`
                ADD COLUMN `error_code` TINYINT(3) NULL DEFAULT NULL AFTER `message`,
                DROP COLUMN `response`;
        ");
    }

    public function down(): void
    {
        $this->execute("
            ALTER TABLE `log_sms`
                ADD COLUMN `response` TEXT NULL DEFAULT NULL AFTER `message`,
                DROP COLUMN `error_code`;
        ");
    }
}
