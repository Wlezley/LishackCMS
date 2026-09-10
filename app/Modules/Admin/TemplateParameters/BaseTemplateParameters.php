<?php

declare(strict_types=1);

namespace App\Modules\Admin\TemplateParameters;

use Nette\Security\User;

class BaseTemplateParameters extends \Nette\Bridges\ApplicationLatte\Template
{
    public string $title;
    public string $baseUrl;
    public string $basePath;
    public string $activeMenu;

    /** @var array<mixed> $flashes */
    public array $flashes;

    public User $user;
    /** @var array<string, mixed> $userData */
    public array $userData;

    // TODO: Fix naming conventions
    // phpcs:disable Squiz.NamingConventions.ValidVariableName
    // phpcs:disable PSR2.Classes.PropertyDeclaration.Underscore
    public \Closure $_;
    public \Closure $_C;
    public \Closure $_F;
    // phpcs:enable PSR2.Classes.PropertyDeclaration.Underscore
    public string $VERSION;
    public string $HTML_LANG;
    public string $DEFAULT_LANG;
    public string $DEFAULT_LANG_ADMIN;
    public string $DEFAULT_LANG_TINYMCE;
    public int $js_version;
    public int $css_version;
    public int $js_version_tinymce;
    public int $css_version_tinymce;
    // phpcs:enable Squiz.NamingConventions.ValidVariableName
}
