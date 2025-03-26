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
    protected string $htmlLang;

    public function startup(): void
    {
        parent::startup();

        // TODO: Get language from URL or session
        $this->lang = $this->c('DEFAULT_LANG_ADMIN');
        $this->translationManager->setCurrentLanguage($this->lang);
        $this->htmlLang = $this->translationManager->getLanguageService()->getLanguage($this->lang)['html_lang'] ?? $this->lang;
    }

    public function beforeRender(): void
    {
        parent::beforeRender();

        // Configuration
        $this->template->_C = fn($key) => $this->configManager->get($key);

        // Translations
        $this->template->_ = fn($key) => $this->translationManager->get($key, $this->lang);

        // Translated Title
        $this->template->title = $this->getPresenterTitle();

        // CMS config
        $this->template->VERSION = VERSION; // $this->c('VERSION');
        $this->template->HTML_LANG = $this->htmlLang;

        // TODO: Get language from URL or session
        $this->template->DEFAULT_LANG = $this->c('DEFAULT_LANG');
        $this->template->DEFAULT_LANG_ADMIN = $this->c('DEFAULT_LANG_ADMIN');
        $this->template->DEFAULT_LANG_TINYMCE = $this->c('DEFAULT_LANG_ADMIN');
    }

    public function afterRender(): void
    {
        parent::afterRender();

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
     * Translates a key and formats the translation with the given values.
     *
     * This is a wrapper around `TranslationManager::getf()`, which:
     * - Retrieves the translated string for the given key.
     * - Uses `vsprintf()` to format the string with the provided values.
     * - Falls back to returning the key if the translation is missing or formatting fails.
     *
     * @param string $key The translation key.
     * @param mixed ...$values Values to be formatted into the translated string.
     * @return string The formatted translated text, or the key itself if translation is unavailable.
     * @throws \RuntimeException If `TranslationManager` is not available.
     */
    public function tf(string $key, mixed ...$values): string
    {
        if (!isset($this->translationManager)) {
            throw new \RuntimeException('TranslationManager is not available in ' . static::class);
        }

        return $this->translationManager->getf($key, null, $values);
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

    /**
     * Returns the translated title for the current presenter and action.
     *
     * The translation key is generated as `title.{presenter}.{action}`.
     *
     * Example:
     * - Presenter: `Admin:Dashboard`
     * - Action: `default`
     * - Key: `title.admin.dashboard.default`
     *
     * @param string|null $lang Optional language code (defaults to current language).
     * @return string The translated title.
     */
    protected function getPresenterTitle(?string $lang = null): string
    {
        $translationKey = strtolower('title.' . str_replace(':', '.', $this->getName()) . '.' . $this->getAction());
        return $this->translationManager->get($translationKey, $lang);
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
