<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260820221451 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Insert default CMS configuration';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            <<<'SQL'
                INSERT INTO cms_config (`key`, category, value)
                VALUES
                    ('APP_NAME', 'SYS', 'Lishack CMS'),
                    ('CSS_INJECT', 'SYS', ''),
                    ('DEFAULT_LANG', 'SYS', 'cz'),
                    ('DEFAULT_LANG_ADMIN', 'SYS', 'cz'),
                    ('DEFAULT_LOCALE', 'SYS', 'cs_CZ'),
                    ('DEFAULT_PAGE', 'SYS', 'home'),
                    ('JS_INJECT_BODY_FIRST', 'SYS', ''),
                    ('JS_INJECT_BODY_LAST', 'SYS', ''),
                    ('JS_INJECT_HEAD', 'SYS', ''),
                    ('JS_IP_EXCEPTIONS', 'SYS', '127.0.0.1, ::1, 192.168.0.1/24'),
                    ('LOG_TRANSLATION_FALLBACK', 'SYS', '1'),
                    ('OG_DESCRIPTION', 'SOCIAL', 'Content management system built on the Nette Framework 3.2'),
                    ('OG_IMAGE', 'SOCIAL', 'https://dummyimage.com/1200x630/fa3/fff'),
                    ('OG_SHOW_LOCALE', 'SOCIAL', '1'),
                    ('OG_TITLE', 'SOCIAL', 'Lishack CMS (soc)'),
                    ('PAGINATION_MAX_PAGES', 'SYS', '20'),
                    ('PAGINATION_PAGE_ITEMS', 'SYS', '10'),
                    ('RECAPTCHA_SECRET', 'SYS', ''),
                    ('RECAPTCHA_SITE_KEY', 'SYS', ''),
                    ('SEO_DESCRIPTION', 'SEO', 'Content management system built on the Nette Framework 3.2'),
                    ('SEO_INDEX', 'SEO', 'index, follow'),
                    ('SEO_ROBOTS', 'SEO', 'User-agent: *
                Allow: /

                Sitemap: https://www.example.com/sitemap.xml'),
                    ('SEO_TITLE', 'SEO', 'Lishack CMS'),
                    ('SITE_TITLE', 'SYS', 'Lishack CMS (site)')
                SQL
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql(
            <<<'SQL'
                DELETE FROM cms_config
                WHERE `key` IN (
                    'APP_NAME',
                    'CSS_INJECT',
                    'DEFAULT_LANG',
                    'DEFAULT_LANG_ADMIN',
                    'DEFAULT_LOCALE',
                    'DEFAULT_PAGE',
                    'JS_INJECT_BODY_FIRST',
                    'JS_INJECT_BODY_LAST',
                    'JS_INJECT_HEAD',
                    'JS_IP_EXCEPTIONS',
                    'LOG_TRANSLATION_FALLBACK',
                    'OG_DESCRIPTION',
                    'OG_IMAGE',
                    'OG_SHOW_LOCALE',
                    'OG_TITLE',
                    'PAGINATION_MAX_PAGES',
                    'PAGINATION_PAGE_ITEMS',
                    'RECAPTCHA_SECRET',
                    'RECAPTCHA_SITE_KEY',
                    'SEO_DESCRIPTION',
                    'SEO_INDEX',
                    'SEO_ROBOTS',
                    'SEO_TITLE',
                    'SITE_TITLE'
                )
                SQL
        );
    }
}
