<?php

declare(strict_types=1);

namespace App\Modules\Website\Presenters;

use App\Components\IAdminButtonFactory;
use App\Components\IMenuFactory;
use App\Components\IPaginationFactory;
use App\Models\Config;
use App\Models\Helpers\AssetsVersion;
use App\Models\TranslationManager;
use Nette;
use Nette\Database\Explorer;

abstract class BasePresenter extends Nette\Application\UI\Presenter
{
    protected Nette\Http\UrlScript $url;

    /** @var Explorer @inject */
    public Explorer $db;

    /** @var Config @inject */
    public Config $config;

    /** @var TranslationManager @inject */
    public TranslationManager $translationManager;

    /** @var IAdminButtonFactory @inject */
    public IAdminButtonFactory $adminBarFactory;

    /** @var IMenuFactory @inject */
    public IMenuFactory $menuFactory;

    /** @var IPaginationFactory @inject */
    public IPaginationFactory $paginationFactory;

    // Pagination
    private ?int $itemsPerPage = null;
    private ?int $totalItems = null;

    /** @var array<string,string> $cmsConfig */
    protected array $cmsConfig = [];

    protected string $baseUrl;
    protected string $currentUrl;
    protected string $adminUrl;
    protected string $lang = '';
    protected string $page = '';
    protected string $title = '';
    protected string $seo_robots = '';
    protected string $seo_description = '';
    protected string $seo_canonical = '';
    protected string $social_title = '';
    protected string $social_description = '';
    protected string $social_image = '';


    public function startup(): void
    {
        parent::startup();

        // CMS config
        $this->cmsConfig = $this->config->getValues();

        // Url
        $this->url = $this->getHttpRequest()->getUrl();
        $this->baseUrl = $this->url->getBaseUrl();
        $this->currentUrl = $this->url->getAbsoluteUrl();
        $this->adminUrl = ADMIN_HOME_URL;

        // Page settings
        $this->lang = strtolower($this->cmsConfig['DEFAULT_LANG']);
        $this->page = DEFAULT_PAGE;
        $this->title = $this->cmsConfig['SITE_TITLE'];

        // Translations language
        $this->translationManager->setLanguage($this->lang);

        // SEO
        $this->seo_robots = DEBUG ? 'noindex, nofollow' : 'index, follow';
        $this->seo_description = 'seo_description';
        $this->seo_canonical = $this->currentUrl;
        $this->social_title = 'social_title';
        $this->social_description = 'social_description';
        $this->social_image = 'social_image';
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

        // Url
        $this->template->url = $this->url;
        $this->template->currentUrl = $this->currentUrl;

        // Page settings
        $this->template->lang = $this->lang;
        $this->template->html_lang = ($this->lang == 'cz' ? 'cs' : $this->lang);
        $this->template->default_lang = DEFAULT_LANG;
        $this->template->page = $this->page;
        $this->template->title = $this->title;

        // SEO
        $this->template->seo_robots = $this->seo_robots;
        $this->template->seo_description = $this->seo_description;
        $this->template->seo_canonical = $this->seo_canonical;
        $this->template->social_title = $this->social_title;
        $this->template->social_description = $this->social_description;
        $this->template->social_image = $this->social_image;

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
        $control->setQueryParams($this->getHttpRequest());
        $control->setItemsPerPage($this->itemsPerPage);
        $control->setTotalItems($this->totalItems);
        $control->setCurrentPage((int) $this->getParameter('page', 1));

        return $control;
    }

    // ##########################################
    // ###             COMPONENTS             ###
    // ##########################################

    protected function createComponentAdminButton(): \App\Components\AdminButton
    {
        $control = $this->adminBarFactory->create();
        $control->setAdminUrl($this->adminUrl);
        return $control;
    }

    protected function createComponentMenu(): \App\Components\Menu
    {
        $control = $this->menuFactory->create();
        $control->setLang($this->lang);
        return $control;
    }
}
