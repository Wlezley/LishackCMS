<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AdminSidebarTranslation extends AbstractMigration
{
    public function up(): void
    {
        $this->execute("
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('admin.sidebar.main', 'cz', 'Správa webu');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('admin.sidebar.main', 'en', 'Website management');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('admin.sidebar.main.overview', 'cz', 'Přehled');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('admin.sidebar.main.overview', 'en', 'Overview');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('admin.sidebar.main.article', 'cz', 'Články');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('admin.sidebar.main.article', 'en', 'Articles');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('admin.sidebar.main.article.list', 'cz', 'Přehled článků');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('admin.sidebar.main.article.list', 'en', 'Article list');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('admin.sidebar.main.article.create', 'cz', 'Nový článek');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('admin.sidebar.main.article.create', 'en', 'New article');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('admin.sidebar.main.menu', 'cz', 'Menu');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('admin.sidebar.main.menu', 'en', 'Menu');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('admin.sidebar.main.menu.list', 'cz', 'Položky menu');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('admin.sidebar.main.menu.list', 'en', 'Menu items');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('admin.sidebar.main.menu.create', 'cz', 'Nové menu');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('admin.sidebar.main.menu.create', 'en', 'New menu');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('admin.sidebar.main.data', 'cz', 'Data');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('admin.sidebar.main.data', 'en', 'Data');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('admin.sidebar.main.data.list', 'cz', 'Datové kontejnery');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('admin.sidebar.main.data.list', 'en', 'Data containers');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('admin.sidebar.main.data.create', 'cz', 'Nový kontejner');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('admin.sidebar.main.data.create', 'en', 'New container');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('admin.sidebar.main.config', 'cz', 'Nastavení');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('admin.sidebar.main.config', 'en', 'Configuration');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('admin.sidebar.main.config.users', 'cz', 'Uživatelské účty');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('admin.sidebar.main.config.users', 'en', 'User accounts');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('admin.sidebar.main.config.localization', 'cz', 'Lokalizace');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('admin.sidebar.main.config.localization', 'en', 'Localization');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('admin.sidebar.main.config.email', 'cz', 'E-mail');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('admin.sidebar.main.config.email', 'en', 'E-mail');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('admin.sidebar.main.config.website', 'cz', 'Website');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('admin.sidebar.main.config.website', 'en', 'Website');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('admin.sidebar.main.config.seo', 'cz', 'SEO');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('admin.sidebar.main.config.seo', 'en', 'SEO');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('admin.sidebar.main.config.editor', 'cz', 'Editor nastavení');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('admin.sidebar.main.config.editor', 'en', 'Config editor');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('admin.sidebar.main.config.redirect', 'cz', 'Přesměrování');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('admin.sidebar.main.config.redirect', 'en', 'Redirections');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('admin.sidebar.main.config.debug', 'cz', 'Ladění');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('admin.sidebar.main.config.debug', 'en', 'Debug');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('admin.sidebar.tools', 'cz', 'Nástroje webu');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('admin.sidebar.tools', 'en', 'Website tools');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('admin.sidebar.tools.goto-website', 'cz', 'Přejít na web');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('admin.sidebar.tools.goto-website', 'en', 'Go to website');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('admin.sidebar.user', 'cz', 'Uživatel <strong class=\"text-warning\">%s</strong>');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('admin.sidebar.user', 'en', 'User <strong class=\"text-warning\">%s</strong>');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('admin.sidebar.user.profile', 'cz', 'Můj profil');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('admin.sidebar.user.profile', 'en', 'My profile');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('admin.sidebar.user.signout', 'cz', 'Odhlášení');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('admin.sidebar.user.signout', 'en', 'Sign out');
        ");
    }

    public function down(): void
    {
    }
}
