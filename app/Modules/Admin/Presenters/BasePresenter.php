<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presenters;

use App\Models\ConfigManager;
use App\Models\Helpers\AssetsVersion;
use App\Models\TranslationManager;
use Nette\Application\Helpers;
use Nette\Database\Explorer;

abstract class BasePresenter extends \Nette\Application\UI\Presenter
{
    private const CATEGORY_MAP = [
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

    /** @var Explorer @inject */
    public Explorer $db;

    /** @var ConfigManager @inject */
    public ConfigManager $config;

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
        $this->cmsConfig = $this->config->getConfig();
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

    /**
     * Retrieves a translated text for a given key in a specified language.
     *
     * This is a shorthand wrapper for `TranslationManager::get()`.
     *
     * @param string $key The translation key.
     * @param string|null $lang Optional language code (defaults to current language).
     * @throws \RuntimeException If TranslationManager is not available.
     * @return string The translated text, or the key itself if not found.
     */
    public function t(string $key, ?string $lang = null): string
    {
        if (!isset($this->translationManager)) {
            throw new \RuntimeException('TranslationManager is not available in ' . static::class);
        }

        return $this->translationManager->get($key, $lang);
    }

    /**
     * Gets the category associated with the current presenter.
     *
     * Determines the presenter name and maps it to a predefined category.
     * If no matching category is found, returns an empty string.
     *
     * @return string The category name or an empty string if not found.
     */
    protected function getPresenterCategory(): string
    {
        return self::CATEGORY_MAP[Helpers::splitName($this->getName())[1]] ?? '';
    }

    // ##########################################
    // ###             COMPONENTS             ###
    // ##########################################

    protected function createComponent(string $name): ?\Nette\ComponentModel\IComponent
    {
        $component = parent::createComponent($name);

        if ($component instanceof \App\Components\BaseControl) {
            $component->setTranslationManager($this->translationManager);
        }

        return $component;
    }
}
