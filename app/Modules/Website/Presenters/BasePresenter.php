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
    use \App\Models\Config;
    use \App\Models\Translation;

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

    // URL
    protected Nette\Http\UrlScript $url;

    // Pagination
    private ?int $itemsPerPage = null;
    private ?int $totalItems = null;

    // Page defaults
    protected string $baseUrl;
    protected string $currentUrl;
    protected string $adminUrl;
    protected string $lang;

    /** @var array<string,mixed> $defaultParams */
    protected array $defaultParams = [];


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
            $this->redirectUrl($redirectUrl, $redirectCode ?? Nette\Http\IResponse::S302_Found);
        }

        // Language
        $this->lang = $this->c('DEFAULT_LANG'); // TODO: Get language from URL or session
        $this->translationManager->setCurrentLanguage($this->lang);

        // Default parameters
        $this->defaultParams = [
            'lang' => $this->lang, // TODO: Get language from URL or session
            'HTML_LANG' => $this->translationManager->getLanguageService()->getLanguage($this->lang)['html_lang'] ?? $this->lang,
            'DEFAULT_LANG' => $this->c('DEFAULT_LANG'),
            'page' => $this->c('DEFAULT_PAGE'),
            'title' => $this->c('SITE_TITLE'), // TODO: Use SEO_TITLE instead?
            // 'baseUrl' => $this->baseUrl,
            'currentUrl' => $this->currentUrl,
            'adminUrl' => $this->adminUrl,
            'seo_index' => DEBUG ? 'noindex, nofollow' : $this->c('SEO_INDEX'),
            'seo_title' => $this->c('SEO_TITLE'),
            'seo_description' => $this->c('SEO_DESCRIPTION'),
            'seo_canonical' => $this->url->getHostUrl() . $this->url->getPath(),
            'og_title' => $this->c('OG_TITLE'),
            'og_description' => $this->c('OG_DESCRIPTION'),
            'og_image' => $this->c('OG_IMAGE'),
            'og_show_locale' => ($this->c('OG_SHOW_LOCALE') == 1),
            'og_locale' => $this->translationManager->getLanguageService()->getLanguage($this->lang)['locale'] ?? $this->c('DEFAULT_LOCALE'),
        ];
    }

    public function beforeRender(): void
    {
        parent::beforeRender();

        // Configuration
        $this->template->_C = fn($key) => $this->configManager->get($key);

        // Translations
        $this->template->_ = fn($key) => $this->translationManager->get($key, $this->lang);
        $this->template->_F = fn($key, $values) => $this->translationManager->getf($key, $this->lang, $values);

        // Default parameters
        $this->template->setParameters($this->defaultParams);
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
