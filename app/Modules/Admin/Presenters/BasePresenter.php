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
    public ConfigManager $configManager;

    /** @var TranslationManager @inject */
    public TranslationManager $translationManager;

    /** @var string */
    protected string $lang;

    public function startup(): void
    {
        parent::startup();

        $this->lang = $this->c('DEFAULT_LANG'); // TODO: Get lang from URL, DEFAULT_LANG use here only as fallback

        $this->translationManager->setCurrentLanguage($this->lang);
    }

    public function beforeRender(): void
    {
        parent::beforeRender();

        // Translations
        $this->template->_ = fn($key) => $this->translationManager->get($key, $this->lang);

        // Configuration
        $this->template->_C = fn($key) => $this->configManager->get($key);
    }

    public function afterRender(): void
    {
        parent::afterRender();

        // CMS config
        // $this->template->setParameters($this->configManager->getConfigValues());

        $this->template->VERSION = VERSION; // $this->c('VERSION');

        // TODO: Get lang from URL, DEFAULT_LANG use here only as fallback
        $this->template->HTML_LANG = $this->c('DEFAULT_LANG');
        $this->template->DEFAULT_LANG = $this->c('DEFAULT_LANG');
        $this->template->DEFAULT_LANG_ADMIN = $this->c('DEFAULT_LANG');
        $this->template->DEFAULT_LANG_TINYMCE = $this->c('DEFAULT_LANG');

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
     * Retrieves a configuration value for a given key.
     *
     * This is a shorthand wrapper for `ConfigManager::get()`.
     *
     * @param string $key The configuration key.
     * @throws \RuntimeException If ConfigManager is not available.
     * @return string|null The configuration value, or null if not found.
     */
    public function c(string $key): ?string
    {
        if (!isset($this->configManager)) {
            throw new \RuntimeException('ConfigManager is not available in ' . static::class);
        }

        return $this->configManager->get($key);
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
            $component->setConfigManager($this->configManager);
        }

        return $component;
    }
}
