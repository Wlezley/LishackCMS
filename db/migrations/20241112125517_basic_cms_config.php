<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class BasicCmsConfig extends AbstractMigration
{
    public function up(): void
    {
        $this->execute("
            INSERT INTO `cms_config` (`name`, `category`, `value`) VALUES
                ('APP_NAME', 'SYS', 'Lishack CMS'),
                ('SITE_TITLE', 'SYS', 'Lishack CMS'),
                ('DEFAULT_LANG', 'SYS', 'CZ'),
                ('SEO_INDEX', 'SEO', 'SEO_INDEX'),
                ('SEO_DESCRIPTION', 'SEO', 'SEO_DESCRIPTION'),
                ('SEO_CANONICAL', 'SEO', 'SEO_CANONICAL'),
                ('SOCIAL_TITLE', 'SOCIAL', 'SOCIAL_TITLE'),
                ('SOCIAL_DESCRIPTION', 'SOCIAL', 'SOCIAL_DESCRIPTION'),
                ('SOCIAL_IMAGE', 'SOCIAL', 'SOCIAL_IMAGE'),
                ('LANG', 'SYS', 'CZ');
        ");
    }

    public function down(): void
    {
        $this->execute("TRUNCATE TABLE `cms_config`;");
    }
}
