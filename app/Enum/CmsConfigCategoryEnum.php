<?php

declare(strict_types=1);

namespace App\Enum;

enum CmsConfigCategoryEnum: string
{
    public const int MAX_LENGTH = 6; // TODO: Create PHPunit test for all arrays with this setting

    case Sys = 'SYS';
    case Social = 'SOCIAL';
    case Seo = 'SEO';
}
