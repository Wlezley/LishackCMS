<?php

declare(strict_types=1);

namespace App\Modules\Website\Presenters;

use App\Models\SitemapGenerator;
use Nette\Application\Responses\TextResponse;
use Tracy\Debugger;

final class SeoPresenter extends BasePresenter
{
    /** @var SitemapGenerator @inject */
    public SitemapGenerator $sitemapGenerator;

    public function __construct()
    {
        parent::__construct();

        Debugger::$showBar = false;
    }

    public function actionRobots(): void
    {
        $robots = $this->c('SEO_ROBOTS');

        if ($robots === null) {
            $robots = "User-agent: *\n"
                    . "Disallow: /\n"
                    . "Sitemap: " . HOME_URL . "sitemap.xml?lang={$this->lang}\n";
        }

        $this->getHttpResponse()->setContentType('text/plain');
        $response = new TextResponse($robots);
        $this->sendResponse($response);
    }

    public function actionSitemap(): void
    {
        $this->sitemapGenerator->regenerate();
        $sitemap = $this->sitemapGenerator->getContent();

        $this->getHttpResponse()->setContentType('application/xml');
        $response = new TextResponse($sitemap);
        $this->sendResponse($response);
    }
}
