<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class SmsGateLog extends AbstractMigration
{
    public function up()
    {
        $this->execute("
            CREATE TABLE `log_sms` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `user_id` INT(11) NULL DEFAULT NULL,
                `date` DATETIME NULL DEFAULT current_timestamp(),
                `phone_number` VARCHAR(16) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
                `message` VARCHAR(460) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
                `response` TEXT NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
                PRIMARY KEY (`id`) USING BTREE
            )
            COLLATE='utf8mb4_general_ci'
            ENGINE=InnoDB;
        ");
    }

    public function down()
    {
        $this->execute("DROP TABLE `log_sms`;");
    }
}
