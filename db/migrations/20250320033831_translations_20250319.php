<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class Translations20250319 extends AbstractMigration
{
    public function up(): void
    {
        $this->execute("TRUNCATE TABLE `translations`");

        // Translations data 2025-03-19
        $this->execute("
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('action', 'cs', 'Akce');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('action', 'en', 'Action');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('active', 'cs', 'Aktivní');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('active', 'en', 'Active');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('add', 'cs', 'Přidat');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('add', 'en', 'Add');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('back', 'cs', 'Zpět');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('back', 'en', 'Back');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('created', 'cs', 'Vytvořeno');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('created', 'en', 'Created');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('default', 'cs', 'výchozí');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('default', 'en', 'default');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('delete', 'cs', 'Smazat');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('delete', 'en', 'Delete');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('delete.user', 'cs', 'Smazat uživatele');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('delete.user', 'en', 'Delete user');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('deleted', 'cs', 'Smazaný');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('deleted', 'en', 'Deleted');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('e-mail', 'cs', 'E-mail');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('e-mail', 'en', 'E-mail');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('edit', 'cs', 'Upravit');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('edit', 'en', 'Edit');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('full-name', 'cs', 'Celé jméno');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('full-name', 'en', 'Full name');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('key', 'cs', 'Klíč');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('key', 'en', 'Key');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('last-login', 'cs', 'Poslení přihlášení');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('last-login', 'en', 'Last login');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('last-login.short', 'cs', 'Přihlášení');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('last-login.short', 'en', 'Last login');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('lishack-cms', 'cs', 'Lishack CMS CZ');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('lishack-cms', 'de', 'Lishack CMS DE');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('lishack-cms', 'en', 'Lishack CMS EN');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('name', 'cs', 'Jméno');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('name', 'en', 'Name');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('next', 'cs', 'Další');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('next', 'en', 'Next');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('no', 'cs', 'Ne');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('no', 'en', 'No');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('permissions', 'cs', 'Oprávnění');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('permissions', 'en', 'Permissions');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('previous', 'cs', 'Předchozí');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('previous', 'en', 'Previous');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('save', 'cs', 'Uložit');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('save', 'en', 'Save');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('save.data', 'cs', 'Uložit data');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('save.data', 'en', 'Save data');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('save.translations', 'cs', 'Uložit překlady');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('save.translations', 'en', 'Save translations');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('save.user', 'cs', 'Uložit uživatele');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('save.user', 'en', 'Save user');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('text', 'cs', 'Text');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('text', 'en', 'Text');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('user.role.admin', 'cs', 'Administrátor');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('user.role.admin', 'en', 'Administrator');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('user.role.guest', 'cs', 'Host');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('user.role.guest', 'en', 'Guest');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('user.role.manager', 'cs', 'Moderátor');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('user.role.manager', 'en', 'Manager');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('user.role.redactor', 'cs', 'Redaktor');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('user.role.redactor', 'en', 'Redactor');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('user.role.user', 'cs', 'Uživatel');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('user.role.user', 'en', 'User');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('yes', 'cs', 'Ano');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('yes', 'en', 'Yes');
        ");
    }

    public function down(): void
    {
        // $this->execute("TRUNCATE TABLE `translations`");
    }
}
