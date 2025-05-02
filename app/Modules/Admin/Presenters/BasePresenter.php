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
    use \App\Models\Config;
    use \App\Models\Translation;

    private const CATEGORY_MAP = [
        'Admin' => 'HOME',
        'Article' => 'ARTICLE',
        'Config' => 'CONFIG',
        'Data' => 'DATA',
        'Dataset' => 'CONFIG',
        'Debug' => 'CONFIG',
        'Email' => 'CONFIG',
        'Menu' => 'MENU',
        'Redirect' => 'CONFIG',
        'Translation' => 'CONFIG',
        'User' => 'CONFIG',
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
        $this->template->_F = fn($key, $values) => $this->translationManager->getf($key, $this->lang, $values);

        // Translated Title
        $this->template->title = $this->getPresenterTitle();
    }

    public function afterRender(): void
    {
        parent::afterRender();

        // CMS config
        $this->template->VERSION = VERSION; // $this->c('VERSION');
        $this->template->HTML_LANG = $this->htmlLang;

        // TODO: Get language from URL or session
        $this->template->DEFAULT_LANG = $this->c('DEFAULT_LANG');
        $this->template->DEFAULT_LANG_ADMIN = $this->c('DEFAULT_LANG_ADMIN');
        $this->template->DEFAULT_LANG_TINYMCE = $this->c('DEFAULT_LANG_ADMIN');

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
