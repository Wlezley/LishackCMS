<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class ConfigSeoForm extends AbstractMigration
{
    public function up(): void
    {
        $this->execute("
            INSERT INTO `cms_config` (`key`, `category`, `value`) VALUES ('OG_SHOW_LOCALE', 'SOCIAL', '1');

            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('open-graph.title', 'cz', 'Open Graph - výchozí titulek');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('open-graph.title', 'en', 'Open Graph - default title');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('open-graph.description', 'cz', 'Open Graph - výchozí popis');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('open-graph.description', 'en', 'Open Graph - default description');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('open-graph.image', 'cz', 'Open Graph - výchozí obrázek (URL)');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('open-graph.image', 'en', 'Open Graph - default image (URL)');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('seo.title', 'cz', 'Název webu');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('seo.title', 'en', 'Website title');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('seo.description', 'cz', 'Popis webu');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('seo.description', 'en', 'Website description');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('seo.index', 'cz', 'Nastavení indexování (meta robots)');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('seo.index', 'en', 'Index settings (meta robots)');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('open-graph.show-locale', 'cz', 'Zobrazovat <meta property=\"og:locale\" ...> v sekci HEAD');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('open-graph.show-locale', 'en', 'Show <meta property=\"og:locale\" ...> in the HEAD section');
        ");
    }

    public function down(): void
    {
    }
}
