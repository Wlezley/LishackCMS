<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CmsConfig20250323 extends AbstractMigration
{
    public function up(): void
    {
        $this->execute("TRUNCATE TABLE `cms_config`");

        // CMS Config data 2025-03-23
        $this->execute("
            INSERT INTO `cms_config` (`key`, `category`, `value`) VALUES ('APP_NAME', 'SYS', 'Lishack CMS');
            INSERT INTO `cms_config` (`key`, `category`, `value`) VALUES ('CSS_INJECT', 'SYS', '');
            INSERT INTO `cms_config` (`key`, `category`, `value`) VALUES ('DEFAULT_LANG', 'SYS', 'cz');
            INSERT INTO `cms_config` (`key`, `category`, `value`) VALUES ('DEFAULT_LANG_ADMIN', 'SYS', 'cz');
            INSERT INTO `cms_config` (`key`, `category`, `value`) VALUES ('DEFAULT_LOCALE', 'SYS', 'cs_CZ');
            INSERT INTO `cms_config` (`key`, `category`, `value`) VALUES ('DEFAULT_PAGE', 'SYS', 'home');
            INSERT INTO `cms_config` (`key`, `category`, `value`) VALUES ('JS_INJECT_BODY_FIRST', 'SYS', '');
            INSERT INTO `cms_config` (`key`, `category`, `value`) VALUES ('JS_INJECT_BODY_LAST', 'SYS', '');
            INSERT INTO `cms_config` (`key`, `category`, `value`) VALUES ('JS_INJECT_HEAD', 'SYS', '');
            INSERT INTO `cms_config` (`key`, `category`, `value`) VALUES ('JS_IP_EXCEPTIONS', 'SYS', '127.0.0.1, ::1, 192.168.0.1/24');
            INSERT INTO `cms_config` (`key`, `category`, `value`) VALUES ('OG_DESCRIPTION', 'SOCIAL', 'Content management system built on the Nette Framework 3.2');
            INSERT INTO `cms_config` (`key`, `category`, `value`) VALUES ('OG_IMAGE', 'SOCIAL', 'https://dummyimage.com/1200x630/fa3/fff');
            INSERT INTO `cms_config` (`key`, `category`, `value`) VALUES ('OG_TITLE', 'SOCIAL', 'Lishack CMS (soc)');
            INSERT INTO `cms_config` (`key`, `category`, `value`) VALUES ('PAGINATION_MAX_PAGES', 'SYS', '20');
            INSERT INTO `cms_config` (`key`, `category`, `value`) VALUES ('PAGINATION_PAGE_ITEMS', 'SYS', '10');
            INSERT INTO `cms_config` (`key`, `category`, `value`) VALUES ('RECAPTCHA_SECRET', 'SYS', '');
            INSERT INTO `cms_config` (`key`, `category`, `value`) VALUES ('RECAPTCHA_SITE_KEY', 'SYS', '');
            INSERT INTO `cms_config` (`key`, `category`, `value`) VALUES ('SEO_DESCRIPTION', 'SEO', 'Content management system built on the Nette Framework 3.2');
            INSERT INTO `cms_config` (`key`, `category`, `value`) VALUES ('SEO_INDEX', 'SEO', 'index, follow');
            INSERT INTO `cms_config` (`key`, `category`, `value`) VALUES ('SEO_ROBOTS', 'SEO', 'User-agent: *\nAllow: /\n\nSitemap: https://www.example.com/sitemap.xml');
            INSERT INTO `cms_config` (`key`, `category`, `value`) VALUES ('SEO_TITLE', 'SEO', 'Lishack CMS');
            INSERT INTO `cms_config` (`key`, `category`, `value`) VALUES ('SITE_TITLE', 'SYS', 'Lishack CMS (site)');
        ");
    }

    public function down(): void
    {
    }
}
