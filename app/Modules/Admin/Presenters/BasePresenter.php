<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presenters;

use App\Models\Config;
use App\Models\Helpers\AssetsVersion;
use Nette\Application\Helpers;
use Nette\Database\Explorer;

abstract class BasePresenter extends \Nette\Application\UI\Presenter
{
    /** @var Explorer @inject */
    public $db;

    /** @var Config @inject */
    public $config;

    /** @var array<string,string> $cmsConfig */
    protected array $cmsConfig = [];

    public function startup(): void
    {
        parent::startup();

        // CMS config
        $this->cmsConfig = $this->config->getValues();
    }

    public function afterRender(): void
    {
        parent::afterRender();

        // CMS config
        $this->template->setParameters($this->cmsConfig);
        $this->template->VERSION = VERSION;
        $this->template->HTML_LANG = (DEFAULT_LANG == 'cz' ? 'cs' : DEFAULT_LANG); // @phpstan-ignore equal.alwaysTrue
        $this->template->DEFAULT_LANG = DEFAULT_LANG;
        $this->template->DEFAULT_LANG_ADMIN = DEFAULT_LANG;
        $this->template->DEFAULT_LANG_TINYMCE = (DEFAULT_LANG == 'cz' ? 'cs' : DEFAULT_LANG); // @phpstan-ignore equal.alwaysTrue

        // Assets version
        $assetsVersion = new AssetsVersion();
        $assetsVersion->setTemplate($this->template)
            ->setBasePath(ASSETS_DIR)
            ->addFile('admin/dist/scripts.min.js', 'js_version')
            ->addFile('admin/dist/styles.css', 'css_version')
            ->addFile('tinymce-bundle/dist/scripts.min.js', 'js_version_tinymce')
            ->addFile('tinymce-bundle/dist/styles.css', 'css_version_tinymce');

        // Sidebar
        $this->template->activeMenu = $this->getPresenterCategory();

        bdump($this->template->getParameters(), 'TEMPLATE PARAMS');
    }

    protected function getPresenterCategory(): string
    {
        $presenterName = Helpers::splitName($this->getName())[1];

        $categoryList = [
            'Admin' => 'HOME',
            'Article' => 'ARTICLE',
            'Menu' => 'MENU',
            'Data' => 'DATA',
            'Config' => 'CONFIG',
            'User' => 'CONFIG',
            'Strings' => 'CONFIG',
            'Email' => 'CONFIG',
            'Website' => 'CONFIG',
            'Seo' => 'CONFIG',
            'Redirect' => 'CONFIG',
            'Debug' => 'CONFIG',
        ];

        if (array_key_exists($presenterName, $categoryList)) {
            return $categoryList[$presenterName];
        }

        return '';
    }
}
