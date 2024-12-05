<?php

declare(strict_types=1);

namespace App\Modules\Website\Presenters;

use App\Components\IAdminButtonFactory;
use App\Components\IMenuFactory;
use App\Models\Config;
use App\Models\Menu;
use Nette;
use Nette\Application\UI\Presenter;

abstract class BasePresenter extends Presenter
{
    /** @var Nette\Database\Explorer @inject */
    public $db;

    /** @var Config @inject */
    public $config;

    /** @var Menu @inject */
    public $menu;

    /** @var Nette\Http\UrlScript */
    protected $url;

    // ########### COMPONENTS ###########

    /** @var IAdminButtonFactory @inject */
    public $adminBarFactory;

    /** @var IMenuFactory @inject */
    public $menuFactory;

    // ########### PARAMS ###########

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

    /**
     * @throws Nette\Application\AbortException
     */
    public function startup(): void
    {
        parent::startup();

        // DEBUG ONLY ...
        // $session = $this->getSession('app');
        // bdump($session, 'SESSION');
        // bdump($this->user->isLoggedIn(), 'USER');
        // $menuTree = $this->menu->getMenuTree()[0]['items'];
        // bdump($menuTree, 'MENU TREE');

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

        // SEO
        $this->seo_robots = DEBUG ? 'noindex, nofollow' : 'index, follow';
        $this->seo_description = 'seo_description';
        $this->seo_canonical = $this->currentUrl;
        $this->social_title = 'social_title';
        $this->social_description = 'social_description';
        $this->social_image = 'social_image';
    }

    /**
     * Setup template variables
     */
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
        if (file_exists(ASSETS_DIR . 'website/dist/scripts.min.js')) {
            $this->template->js_version = filemtime(ASSETS_DIR . 'website/dist/scripts.min.js');
        }
        if (file_exists(ASSETS_DIR . 'website/dist/styles-main.css')) {
            $this->template->css_version = filemtime(ASSETS_DIR . 'website/dist/styles-main.css');
        }

        bdump($this->template->getParameters(), 'TEMPLATE PARAMS');

        // TODO: Ajax
        // if ($this->isAjax() && !$this->isControlInvalid()) {
        //     $this->redrawControl();
        // }
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
        $control->setLang($this->lang);
        return $control;
    }
}
