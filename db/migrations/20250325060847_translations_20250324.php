<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class Translations20250324 extends AbstractMigration
{
    public function up(): void
    {
        $this->execute("
            UPDATE `translations` SET `text`='smazat' WHERE `key`='delete' AND `lang`='cz';
            UPDATE `translations` SET `text`='delete' WHERE `key`='delete' AND `lang`='en';
        ");

        $this->execute("
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('login-name', 'cz', 'Přihlašovací jméno');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('login-name', 'en', 'Login name');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('active.user', 'cz', 'Aktivní uživatel');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('active.user', 'en', 'Active user');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('deleted.user', 'cz', 'Smazaný uživatel');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('deleted.user', 'en', 'Deleted user');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('password', 'cz', 'Heslo');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('password', 'en', 'Password');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('password.again', 'cz', 'Heslo znovu');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('password.again', 'en', 'Password again');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('password.change', 'cz', 'Změnit heslo');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('password.change', 'en', 'Change password');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('enabled.user', 'cz', 'Povolený uživatel (smí se přihlásit)');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('enabled.user', 'en', 'Enabled user (can log in)');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('create', 'cz', 'Vytvořit');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('create', 'en', 'Create');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('user.enabled-callback.himself', 'cz', 'Uživatel nemůže %s sám sebe.');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('user.enabled-callback.himself', 'en', 'User do not %s himself.');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('user.enabled-callback.admin', 'cz', 'Hlavního administrátora nelze %s přes administraci.');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('user.enabled-callback.same-role', 'cz', 'Nemůžete %s uživatele, který má stejné (nebo vyšší) oprávnění jako Vy.');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('user.enabled-callback.update-failed', 'cz', 'Vybraného uživatele se nepodařilo %s.');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('enable', 'cz', 'povolit');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('enable', 'en', 'enable');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('disable', 'cz', 'zakázat');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('disable', 'en', 'disable');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('user.enabled-callback.admin', 'en', 'The main administrator cannot be %sd through the administration.');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('user.enabled-callback.same-role', 'en', 'You cannot %s a user who has the same (or higher) permissions as you.');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('user.enabled-callback.update-failed', 'en', 'The selected user could not be %sd.');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('restore', 'cz', 'obnovit');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('restore', 'en', 'restore');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('user.deleted-callback.himself', 'cz', 'Uživatel nemůže %s sám sebe.');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('user.deleted-callback.himself', 'en', 'User do not %s himself.');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('user.deleted-callback.admin', 'cz', 'Hlavního administrátora nelze %s přes administraci.');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('user.deleted-callback.admin', 'en', 'The main administrator cannot be %sd through the administration.');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('user.deleted-callback.same-role', 'cz', 'Nemůžete %s uživatele, který má stejné (nebo vyšší) oprávnění jako Vy.');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('user.deleted-callback.same-role', 'en', 'You cannot %s a user who has the same (or higher) permissions as you.');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('user.deleted-callback.update-failed', 'cz', 'Vybraného uživatele se nepodařilo %s.');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('user.deleted-callback.update-failed', 'en', 'The selected user could not be %sd.');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('user.role-callback.himself', 'cz', 'Uživatel nemůže měnit vlastní oprávnění.');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('user.role-callback.admin', 'cz', 'Oprávnění hlavního administrátora nelze měnit.');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('user.role-callback.same-role', 'cz', 'Nemůžete měnit oprávnění uživatele, který má stejné (nebo vyšší) oprávnění jako Vy.');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('user.role-callback.role-elevation', 'cz', 'Uživateli nelze udělit stejné, ani vyšší oprávnění, než jaké máte Vy.');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('user.role-callback.update-failed', 'cz', 'U vybraného uživatele se nepodařilo změnit oprávnění.');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('user.role-callback.himself', 'en', 'A user cannot change their own permissions.');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('user.role-callback.admin', 'en', 'The main administrator\'s permissions cannot be changed.');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('user.role-callback.same-role', 'en', 'You cannot change the permissions of a user who has the same (or higher) permissions as you.');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('user.role-callback.role-elevation', 'en', 'You cannot grant the same or higher permissions than you have.');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('user.role-callback.update-failed', 'en', 'Permissions could not be changed for the selected user.');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('error', 'cz', 'Chyba');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('error', 'en', 'Error');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('error.form.empty-data', 'cz', 'Formulář odeslal prázdná data.');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('error.form.empty-data', 'en', 'Form sent empty data.');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('error.form.empty-target-lang', 'cz', 'Formulář neodeslal hodnotu cílového jazyka.');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('error.form.empty-target-lang', 'en', 'The form did not submit a target language value.');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('success.form.translations-saved', 'cz', 'Překlady byly uloženy.');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('success.form.translations-saved', 'en', 'The translations have been saved.');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('error.form.missing-required', 'cz', 'Povinné pole \'%s\' je prázdné. Formulář nebyl uložen.');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('error.form.missing-required', 'en', 'Required field \'%s\' is empty. Form was not saved.');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('error.form.missing-required.settings', 'cz', 'Povinná položka nastavení \'%s\' je prázdná. Nastavení nebylo uloženo.');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('error.form.missing-required.settings', 'en', 'The required setting item \'%s\' is empty. The settings was not saved.');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('success.form.settings-saved', 'cz', 'Nastavení bylo uloženo.');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('success.form.settings-saved', 'en', 'The settings has been saved.');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('error.form.redirect.invalid-http-code', 'cz', 'Neplatný HTTP code \'%s\'. Nastavení přesměrování nebylo uloženo.');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('error.form.redirect.invalid-http-code', 'en', 'Invalid HTTP code \'%s\'. Redirect settings was not saved.');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('error.form.unknown-origin', 'cz', 'Neznámý typ formuláře.');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('error.form.unknown-origin', 'en', 'Unknown form type.');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('success.form.redirect-created', 'cz', 'Přesměrování bylo vytvořeno.');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('success.form.redirect-created', 'en', 'The redirect has been created.');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('success.form.redirect-saved', 'cz', 'Změny v přesměrování byly uloženy.');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('success.form.redirect-saved', 'en', 'The redirection changes have been saved.');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('close', 'cz', 'Zavřít');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('close', 'en', 'Close');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('cancel', 'cz', 'Zrušit');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('cancel', 'en', 'Cancel');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('btn.delete', 'cz', 'Smazat');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('btn.delete', 'en', 'Delete');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('modal.title.confirm-delete', 'cz', 'Potvrďte smazání');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('modal.title.confirm-delete', 'en', 'Confirm deletion');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('modal.body.delete-item', 'cz', 'Opravdu chcete položku <strong>%s</strong> smazat?');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('modal.body.delete-item', 'en', 'Are you sure you want to delete <strong>%s</strong>?');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('modal.body.delete-redirect', 'cz', 'Opravdu chcete přesměrování <strong>%s</strong> smazat?');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('modal.body.delete-redirect', 'en', 'Are you sure you want to delete the redirect <strong>%s</strong>?');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('modal.body.delete-translation', 'cz', 'Opravdu chcete překlad <strong>%s</strong> smazat?');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('modal.body.delete-user', 'cz', 'Opravdu chcete uživatele <strong>%s</strong> smazat?');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('modal.body.delete-menu', 'cz', 'Opravdu chcete menu <strong>%s</strong> smazat?');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('modal.body.delete-translation', 'en', 'Are you sure you want to delete the translation <strong>%s</strong>?');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('modal.body.delete-user', 'en', 'Are you sure you want to delete the user <strong>%s</strong>?');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('modal.body.delete-menu', 'en', 'Are you sure you want to delete the menu <strong>%s</strong>?');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('success.form.translation-created', 'cz', 'Překlad byl vytvořen.');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('success.form.translation-created', 'en', 'The translation has been created.');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('success.form.translation-saved', 'cz', 'Překlad byl uložen.');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('success.form.translation-saved', 'en', 'The translation has been saved.');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('success.form.translation-created.named', 'cz', 'Překlad pro <strong>%s</strong> byl vytvořen.');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('success.form.translation-created.named', 'en', 'Translation for <strong>%s</strong> has been created.');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('error.form.translation-create', 'cz', 'Překlad se nepodařilo vytvořit.');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('error.form.translation-create', 'en', 'The translation could not be created.');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('error.form.translation-duplicate-key', 'cz', 'Překlad pro <strong>%s</strong> již existuje. Přejmenujte klíč, nebo upravte stávající %s');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('error.form.translation-duplicate-key', 'en', 'A translation for <strong>%s</strong> already exists. Rename the key or edit the existing %s');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('btn.login', 'cz', 'Přihlásit se');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('btn.login', 'en', 'Log in');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('login.remember', 'cz', 'Zapamatovat přihlášení na 7 dní');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('login.remember', 'en', 'Remember login for 7 days');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('error.form.fill-password', 'cz', 'Vyplňte heslo.');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('error.form.fill-password', 'en', 'Fill in the password.');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('error.form.passwords-not-match', 'cz', 'Hesla se neshodují.');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('error.form.passwords-not-match', 'en', 'The passwords do not match.');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('error.form.user-role-elevation', 'cz', 'Nemůžete udělit stejná nebo vyšší oprávnění, než máte.');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('error.form.user-role-elevation', 'en', 'You cannot grant the same or higher permissions than you have.');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('success.form.user-created', 'cz', 'Uživatel byl vytvořen (ID: %d).');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('success.form.user-created', 'en', 'The user has been created (ID: %d).');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('error.form.no-permissions.user-edit', 'cz', 'Nemáte dostatečná oprávnění pro editaci tohoto uživatele.');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('error.form.no-permissions.user-edit', 'en', 'You do not have sufficient permissions to edit this user.');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('success.form.user-saved', 'cz', 'Uživatel byl uložen.');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('success.form.user-saved', 'en', 'The user has been saved.');
        ");
    }

    public function down(): void
    {
    }
}
