<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AdminPresentersTitleTranslation extends AbstractMigration
{
    public function up(): void
    {
        $this->execute("
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('title.admin.admin.default', 'cz', 'Přehled');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('title.admin.admin.default', 'en', 'Overview');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('title.admin.article.default', 'cz', 'Seznam článků');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('title.admin.article.default', 'en', 'Article list');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('title.admin.article.create', 'cz', 'Vytvoření nového článku');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('title.admin.article.create', 'en', 'Create new article');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('title.admin.article.edit', 'cz', 'Editace článku');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('title.admin.article.edit', 'en', 'Edit article');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('title.admin.config.editor', 'cz', 'Editor nastavení');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('title.admin.config.editor', 'en', 'Configuration editor');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('title.admin.config.website', 'cz', 'Nastavení Website');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('title.admin.config.website', 'en', 'Website settings');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('title.admin.config.seo', 'cz', 'Nastavení SEO');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('title.admin.config.seo', 'en', 'SEO settings');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('title.admin.data.default', 'cz', 'Seznam datových kontejnerů');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('title.admin.data.default', 'en', 'List of data containers');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('title.admin.data.create', 'cz', 'Vytvoření datového kontejneru');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('title.admin.data.create', 'en', 'Create data container');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('title.admin.data.edit', 'cz', 'Editace datového kontejneru');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('title.admin.data.edit', 'en', 'Edit data container');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('title.admin.debug.default', 'cz', 'Ladění');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('title.admin.debug.default', 'en', 'Debug');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('title.admin.email.default', 'cz', 'E-mail');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('title.admin.email.default', 'en', 'E-mail');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('title.admin.menu.default', 'cz', 'Menu');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('title.admin.menu.default', 'en', 'Menu');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('title.admin.menu.create', 'cz', 'Vytvoření menu');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('title.admin.menu.create', 'en', 'Create new menu');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('title.admin.menu.edit', 'cz', 'Editace menu');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('title.admin.menu.edit', 'en', 'Edit menu');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('title.admin.redirect.default', 'cz', 'Přesměrování');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('title.admin.redirect.default', 'en', 'Redirections');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('title.admin.redirect.create', 'cz', 'Vytvořit přesměrování');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('title.admin.redirect.create', 'en', 'Create redirection');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('title.admin.redirect.edit', 'cz', 'Editace přesměrování');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('title.admin.redirect.edit', 'en', 'Edit redirection');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('title.admin.redirect.import', 'cz', 'Import přesměrování');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('title.admin.redirect.import', 'en', 'Import redirections');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('title.admin.redirect.export', 'cz', 'Export přesměrování');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('title.admin.redirect.export', 'en', 'Export redirections');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('title.admin.sign.in', 'cz', 'Admin Login CZ');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('title.admin.sign.in', 'en', 'Admin Login EN');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('title.admin.translation.default', 'cz', 'Lokalizace');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('title.admin.translation.editor', 'cz', 'Editor Lokalizace');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('title.admin.translation.create', 'cz', 'Nový překlad');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('title.admin.translation.edit', 'cz', 'Editace překladu');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('title.admin.translation.default', 'en', 'Localization');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('title.admin.translation.editor', 'en', 'Localization Editor');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('title.admin.translation.create', 'en', 'Create new translation');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('title.admin.translation.edit', 'en', 'Edit translation');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('title.admin.user.default', 'cz', 'Uživatelské účty');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('title.admin.user.create', 'cz', 'Vytvoření nového uživatele');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('title.admin.user.edit', 'cz', 'Editace uživatele');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('title.admin.user.default', 'en', 'User accounts');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('title.admin.user.create', 'en', 'Create new user');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('title.admin.user.edit', 'en', 'Edit user');
        ");
    }

    public function down(): void
    {
    }
}
