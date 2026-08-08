<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class Translations20250323 extends AbstractMigration
{
    public function up(): void
    {
        // Translations data 2025-03-23
        $this->execute("
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('editor', 'cs', 'Editor');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('editor', 'en', 'Editor');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('search.key', 'cs', 'Vyhledat klíč');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('search.key', 'en', 'Search key');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('search.translation', 'cs', 'Vyhledat překlad');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('search.translation', 'en', 'Search translation');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('save.config', 'cs', 'Uložit nastavení');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('save.config', 'en', 'Save config');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('category', 'cs', 'Kategorie');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('category', 'en', 'Category');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('value', 'cs', 'Hodnota');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('value', 'en', 'Value');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('app.name', 'cs', 'Název aplikace');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('app.name', 'en', 'Application name');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('site.title', 'cs', 'Titulek webu');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('site.title', 'en', 'Website title');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('default_lang.website', 'cs', 'Výchozí jazyk webu');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('default_lang.website', 'en', 'Default website language');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('default_lang.admin', 'cs', 'Výchozí jazyk administrace');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('default_lang.admin', 'en', 'Default administration language');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('recaptcha.site_key', 'cs', 'reCAPTCHA site key');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('recaptcha.site_key', 'en', 'reCAPTCHA site key');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('recaptcha.secret', 'cs', 'reCAPTCHA secret');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('recaptcha.secret', 'en', 'reCAPTCHA secret');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('pagination.page_items', 'cs', 'Počet položek na stránku');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('pagination.page_items', 'en', 'Items per page');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('pagination.max_pages', 'cs', 'Maximální počet stránek');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('pagination.max_pages', 'en', 'Maximum pages');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('js_inject.head', 'cs', 'JavaScript v <head>');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('js_inject.head', 'en', 'JavaScript in <head>');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('js_inject.body_first', 'cs', 'JavaScript na začátku <body>');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('js_inject.body_first', 'en', 'JavaScript at the start of <body>');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('js_inject.body_last', 'cs', 'JavaScript na konci <body>');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('js_inject.body_last', 'en', 'JavaScript at the end of <body>');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('js_inject.ip_exceptions', 'cs', 'Nespouštět JavaScript pro IP (nebo rozsah IP)');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('js_inject.ip_exceptions', 'en', 'JavaScript exceptions for IPs (or IP range)');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('css_inject', 'cs', 'CSS v <head>');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('css_inject', 'en', 'CSS in <head>');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('robots.txt', 'cs', 'Soubor robots.txt');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('robots.txt', 'en', 'File robots.txt');
        ");

        $this->execute("
            ALTER TABLE `lang`
                ADD COLUMN `html_lang` VARCHAR(3) NULL DEFAULT NULL AFTER `name`,
                ADD COLUMN `locale` VARCHAR(6) NULL DEFAULT NULL AFTER `html_lang`;

            UPDATE `lang` SET `html_lang`='cs' WHERE `lang`='cs';
            UPDATE `lang` SET `html_lang`='en' WHERE `lang`='en';
            UPDATE `lang` SET `html_lang`='de' WHERE `lang`='de';
            UPDATE `lang` SET `locale`='cs_CZ' WHERE `lang`='cs';
            UPDATE `lang` SET `locale`='en_US' WHERE `lang`='en';
            UPDATE `lang` SET `locale`='de_DE' WHERE `lang`='de';

            UPDATE `lang` SET `lang`='cz' WHERE `lang`='cs';
            UPDATE `translations` SET `lang`='cz' WHERE `lang`='cs';
            UPDATE `cms_config` SET `value`='cz' WHERE `key`='DEFAULT_LANG';
            UPDATE `cms_config` SET `value`='cz' WHERE `key`='DEFAULT_LANG_ADMIN';
        ");
    }

    public function down(): void
    {
        $this->execute("
            ALTER TABLE `lang`
                DROP COLUMN `html_lang`,
                DROP COLUMN `locale`;

            UPDATE `lang` SET `lang`='cs' WHERE `lang`='cz';
            UPDATE `translations` SET `lang`='cs' WHERE `lang`='cz';
            UPDATE `cms_config` SET `value`='cs' WHERE `key`='DEFAULT_LANG';
            UPDATE `cms_config` SET `value`='cs' WHERE `key`='DEFAULT_LANG_ADMIN';
        ");
    }
}
