<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class Translations20250325 extends AbstractMigration
{
    public function up(): void
    {
        $this->execute("
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('menu-order', 'cz', 'Řazení menu');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('menu-order', 'en', 'Menu order');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('import', 'cz', 'Import');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('import', 'en', 'Import');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('export', 'cz', 'Export');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('export', 'en', 'Export');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('application', 'cz', 'Aplikace');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('application', 'en', 'Application');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('language', 'cz', 'Jazyk');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('language', 'en', 'Language');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('pagination', 'cz', 'Stránkování');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('pagination', 'en', 'Pagination');
        ");
    }

    public function down(): void
    {
    }
}
