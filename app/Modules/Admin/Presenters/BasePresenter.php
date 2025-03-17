<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presenters;

use App\Models\Config;
use App\Models\Helpers\AssetsVersion;
use App\Models\TranslationManager;
use Nette\Application\Helpers;
use Nette\Database\Explorer;

abstract class BasePresenter extends \Nette\Application\UI\Presenter
{
    /** @var Explorer @inject */
    public Explorer $db;

    /** @var Config @inject */
    public Config $config;

    /** @var TranslationManager @inject */
    public TranslationManager $translationManager;

    /** @var array<string,string> $cmsConfig */
    protected array $cmsConfig = [];

    /** @var string */
    protected string $lang;

    public function startup(): void
    {
        parent::startup();

        // CMS config
        $this->cmsConfig = $this->config->getValues();
        $this->lang = DEFAULT_LANG;
        $this->translationManager->setCurrentLanguage($this->lang);
    }

    public function beforeRender(): void
    {
        parent::beforeRender();

        // Translations
        $this->template->_ = fn($key) => $this->translationManager->get($key, $this->lang);
    }

    public function afterRender(): void
    {
        parent::afterRender();

        // CMS config
        $this->template->setParameters($this->cmsConfig);
        $this->template->VERSION = VERSION;
        $this->template->HTML_LANG = DEFAULT_LANG;
        $this->template->DEFAULT_LANG = DEFAULT_LANG;
        $this->template->DEFAULT_LANG_ADMIN = DEFAULT_LANG;
        $this->template->DEFAULT_LANG_TINYMCE = DEFAULT_LANG;

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

        // bdump($this->template->getParameters(), 'TEMPLATE PARAMS');

        // Ajax
        if ($this->isAjax() && !$this->isControlInvalid()) {
            $this->redrawControl();
        }
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
            'Translation' => 'CONFIG',
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
