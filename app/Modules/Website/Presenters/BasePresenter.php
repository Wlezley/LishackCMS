<?php

declare(strict_types=1);

namespace App\Modules\Website\Presenters;

use App\Components\IAdminButtonFactory;
use App\Components\IMenuFactory;
use App\Components\IPaginationFactory;
use App\Models\ConfigManager;
use App\Models\Helpers\AssetsVersion;
use App\Models\Helpers\IPValidator;
use App\Models\RedirectManager;
use App\Models\TranslationManager;
use Nette;
use Nette\Database\Explorer;

abstract class BasePresenter extends Nette\Application\UI\Presenter
{
    protected Nette\Http\UrlScript $url;

    /** @var Explorer @inject */
    public Explorer $db;

    /** @var ConfigManager @inject */
    public ConfigManager $configManager;

    /** @var TranslationManager @inject */
    public TranslationManager $translationManager;

    /** @var RedirectManager @inject */
    public RedirectManager $redirectManager;

    /** @var IAdminButtonFactory @inject */
    public IAdminButtonFactory $adminBarFactory;

    /** @var IMenuFactory @inject */
    public IMenuFactory $menuFactory;

    /** @var IPaginationFactory @inject */
    public IPaginationFactory $paginationFactory;

    // Pagination
    private ?int $itemsPerPage = null;
    private ?int $totalItems = null;

    // Page defaults
    protected string $baseUrl;
    protected string $currentUrl;
    protected string $adminUrl;
    protected string $lang;
    protected string $htmlLang;
    protected string $page;
    protected string $title;
    protected string $seo_index;
    protected string $seo_title;
    protected string $seo_description;
    protected string $seo_canonical;
    protected string $og_title;
    protected string $og_description;
    protected string $og_image;
    protected string $og_locale;
    protected bool $og_show_locale;


    public function startup(): void
    {
        parent::startup();

        // Url
        $this->url = $this->getHttpRequest()->getUrl();
        $this->baseUrl = $this->url->getBaseUrl();
        $this->currentUrl = $this->url->getAbsoluteUrl();
        $this->adminUrl = ADMIN_HOME_URL;

        // Redirect
        $redirectCode = 0;
        $redirectUrl = $this->redirectManager->get($this->currentUrl, $redirectCode);
        if ($redirectUrl) {
            $this->redirectUrl($redirectUrl, $redirectCode ?? 302);
        }

        // Page settings
        $this->lang = $this->c('DEFAULT_LANG'); // TODO: Get language from URL or session
        $this->htmlLang = $this->translationManager->getLanguageService()->getLanguage($this->lang)['html_lang'] ?? $this->lang;
        $this->page = $this->c('DEFAULT_PAGE');
        $this->title = $this->c('SITE_TITLE'); // TODO: Use SEO_TITLE instead?

        // Translations language
        $this->translationManager->setCurrentLanguage($this->lang);

        // SEO (TODO: Read overloads from atricles)
        $this->seo_index = DEBUG ? 'noindex, nofollow' : $this->c('SEO_INDEX');
        $this->seo_title = $this->c('SEO_TITLE');
        $this->seo_description = $this->c('SEO_DESCRIPTION');
        $this->seo_canonical = $this->currentUrl;

        // Open Graph data (TODO: Read overloads from atricles)
        $this->og_title = $this->c('OG_TITLE');
        $this->og_description = $this->c('OG_DESCRIPTION');
        $this->og_image = $this->c('OG_IMAGE');
        $this->og_show_locale = $this->c('OG_SHOW_LOCALE') == 1;
        if ($this->og_show_locale) {
            $this->og_locale = $this->translationManager->getLanguageService()->getLanguage($this->lang)['locale'] ?? $this->c('DEFAULT_LOCALE');
        }
    }

    public function beforeRender(): void
    {
        parent::beforeRender();

        // Configuration
        $this->template->_C = fn($key) => $this->configManager->get($key);

        // Translations
        $this->template->_ = fn($key) => $this->translationManager->get($key, $this->lang);
        $this->template->_F = fn($key, $values) => $this->translationManager->getf($key, $this->lang, $values);

        // Url
        $this->template->url = $this->url;
        $this->template->currentUrl = $this->currentUrl;

        // Page settings
        $this->template->lang = $this->lang;
        $this->template->HTML_LANG = $this->htmlLang;
        $this->template->DEFAULT_LANG = $this->c('DEFAULT_LANG');
        $this->template->page = $this->page;
        $this->template->title = $this->title;

        // SEO
        $this->template->seo_index = $this->seo_index;
        $this->template->seo_title = $this->seo_title;
        $this->template->seo_description = $this->seo_description;
        $this->template->seo_canonical = $this->seo_canonical;

        // Social networks (Open Graph data)
        $this->template->og_title = $this->og_title;
        $this->template->og_description = $this->og_description;
        $this->template->og_image = $this->og_image;
        $this->template->og_locale = $this->og_show_locale ? $this->og_locale : '';
    }

    public function afterRender(): void
    {
        parent::afterRender();

        // JS + CSS code injecting
        $this->template->cssInject = $this->c('CSS_INJECT');
        if (!IPValidator::ipInList($_SERVER['REMOTE_ADDR'], explode(',', $this->c('JS_IP_EXCEPTIONS')))) {
            $this->template->jsInjectHead = $this->c('JS_INJECT_HEAD');
            $this->template->jsInjectBodyFirst = $this->c('JS_INJECT_BODY_FIRST');
            $this->template->jsInjectBodyLast = $this->c('JS_INJECT_BODY_LAST');
        }

        // Assets version
        $assetsVersion = new AssetsVersion();
        $assetsVersion->setTemplate($this->template)
            ->setBasePath(ASSETS_DIR)
            ->addFile('website/dist/scripts.min.js', 'js_version')
            ->addFile('website/dist/styles-main.css', 'css_version')
            ->addFile('website/dist/styles-print.css', 'css_version_print');

        bdump($this->template->getParameters(), 'TEMPLATE PARAMS');

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

    // ##########################################
    // ###             PAGINATION             ###
    // ##########################################

    protected function setPagination(int $itemsPerPage, int $totalItems): void
    {
        $this->itemsPerPage = $itemsPerPage;
        $this->totalItems = $totalItems;
    }

    protected function createComponentPagination(): \App\Components\Pagination
    {
        if ($this->itemsPerPage === null || $this->totalItems === null) {
            throw new \LogicException('Call setPagination() in the render method first.');
        }

        $control = $this->paginationFactory->create();
        $control->setQueryParams($this->getHttpRequest()->getQuery());
        $control->setItemsPerPage($this->itemsPerPage);
        $control->setTotalItems($this->totalItems);
        $control->setCurrentPage((int) $this->getParameter('page', 1));

        return $control;
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

    protected function createComponentAdminButton(): \App\Components\AdminButton
    {
        $control = $this->adminBarFactory->create();
        $control->setAdminUrl($this->adminUrl);
        return $control;
    }

    protected function createComponentMenu(): \App\Components\Menu
    {
        $control = $this->menuFactory->create();
        return $control;
    }
}
