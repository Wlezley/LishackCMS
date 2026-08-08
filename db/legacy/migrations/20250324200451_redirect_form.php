<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class RedirectForm extends AbstractMigration
{
    public function up(): void
    {
        $this->execute("
            ALTER TABLE `redirect`
                ADD COLUMN `id` INT(11) NOT NULL AUTO_INCREMENT FIRST,
                ADD PRIMARY KEY (`id`);

            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('id', 'cz', 'ID');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('id', 'en', 'ID');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('source-url', 'cz', 'Zdrojová URL');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('source-url', 'en', 'Source URL');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('target-url', 'cz', 'Cílová URL');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('target-url', 'en', 'Target URL');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('http-code', 'cz', 'HTTP kód');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('http-code', 'en', 'HTTP code');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('enabled', 'cz', 'Povoleno');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('enabled', 'en', 'Enabled');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('required-items', 'cz', 'Povinné položky');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('required-items', 'en', 'Required items');
        ");
    }

    public function down(): void
    {
        $this->execute("
            ALTER TABLE `redirect`
                AUTO_INCREMENT=0,
                DROP COLUMN `id`,
                DROP PRIMARY KEY;
        ");
    }
}
