<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presenters;

use App\Exception\TranslationException;
use App\Models\Config\ConfigManager;
use App\Models\Config\ConfigTrait;
use App\Models\Helpers\AssetsVersion;
use App\Models\Translation\TranslationManager;
use App\Models\Translation\TranslationTrait;
use Nette\Application\Helpers;
use Nette\Database\Explorer;
use Webmozart\Assert\Assert;

abstract class BasePresenter extends \Nette\Application\UI\Presenter
{
    use ConfigTrait;
    use TranslationTrait;

    /** @var array<string,string> */
    private const array CATEGORY_MAP = [
        'Admin' => 'HOME',
        'Article' => 'ARTICLE',
        'Config' => 'CONFIG',
        'Data' => 'DATA',
        'Dataset' => 'CONFIG',
        'Debug' => 'CONFIG',
        'Email' => 'CONFIG',
        'FileExplorer' => 'STORAGE',
        'Gallery' => 'STORAGE',
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

    protected string $lang;
    protected string $htmlLang;

    /**
     * @throws TranslationException
     */
    public function startup(): void
    {
        parent::startup();

        $lang = $this->c('DEFAULT_LANG_ADMIN'); // TODO: Get language from URL or session
        Assert::notNull($lang, 'Default admin language not found');
        $this->lang = $lang;
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
        Assert::stringNotEmpty($this->getName(), 'Presenter name cannot be empty');
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
     * @throws TranslationException
     */
    protected function getPresenterTitle(?string $lang = null): string
    {
        $name = $this->getName();
        Assert::stringNotEmpty($name, 'Presenter name cannot be empty');

        $action = $this->getAction();
        Assert::stringNotEmpty($action, 'Presenter action cannot be empty');

        $translationKey = 'title.' . str_replace(':', '.', $name) . '.' . $action;
        $translationKey = strtolower($translationKey);
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
