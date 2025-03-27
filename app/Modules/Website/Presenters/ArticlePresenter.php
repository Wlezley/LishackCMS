<?php

declare(strict_types=1);

namespace App\Modules\Website\Presenters;

use App\Models\ArticleManager;
use Nette;

final class ArticlePresenter extends BasePresenter
{
    /** @var ArticleManager @inject */
    public ArticleManager $articleManager;

    /** @todo Translate error messages */
    public function renderDetail(string $articleUrl = '', string $categoryUrl = ''): void
    {
        // URL CHECK ---->>
        $categoryUrlListRaw = explode('/', $categoryUrl);
        $categoryUrlList = array_values(array_filter($categoryUrlListRaw));

        // Broken Category URL will redirect to a fixed URL
        if (!empty($categoryUrl) && count($categoryUrlListRaw) !== count($categoryUrlList)) {
            $this->redirect('this', [
                'articleUrl' => $articleUrl,
                'categoryUrl' => implode('/', $categoryUrlList),
            ]);
        }

        if (empty($categoryUrlList)) {
            if ($articleUrl === $this->c('DEFAULT_PAGE')) {
                $this->redirect('this', []);
            }

            if ($articleUrl == '404') {
                $this->getHttpResponse()->setCode(Nette\Http\IResponse::S404_NotFound);
            }
        }

        if (empty($articleUrl)) {
            if (!empty($categoryUrlList)) {
                $this->redirect('this', []);
            }

            $articleUrl = $this->c('DEFAULT_PAGE');
        }
        // <<---- URL CHECK

        // GET ARTICLE CATEGORY ID
        $articleCategoryId = null;
        try {
            $articleCategoryId = $this->articleManager->findArticleCategoryId($categoryUrlList);
        } catch (\App\Models\ArticleException $e) {
            $this->error($e->getMessage(), $e->getCode());
            // $this->error('Článek nebyl v kategoriích nalezen.', \Nette\Http\IResponse::S404_NotFound);
        }

        // GET ARTICLE ID
        $articleId = null;
        try {
            $articleId = $this->articleManager->findAtricleId($articleUrl, $articleCategoryId);
        } catch (\App\Models\ArticleException $e) {
            $this->error($e->getMessage(), $e->getCode());
            // $this->error('ID Článku nebylo nalezeno.', Nette\Http\IResponse::S404_NotFound);
        }

        // GET ARTICLE DATA
        $articleData = [];
        try {
            $articleData = $this->articleManager->getArticleData($articleId);
        } catch (\App\Models\ArticleException $e) {
            $this->error($e->getMessage(), $e->getCode());
            // $this->error('Článek nebyl nalezen.', Nette\Http\IResponse::S404_NotFound);
        }

        if ($articleData['published'] != 1) {
            $this->error('Článek nebyl publikován.', Nette\Http\IResponse::S404_NotFound);
        }

        // ARTICLE ATTRIBUTES
        $this->template->title = $articleData['title'];
        $this->template->robots = $articleData['robots'];
        $this->template->canonical_url = $articleData['canonical_url'];
        $this->template->og_title = $articleData['og_title'];
        $this->template->og_description = $articleData['og_description'];
        $this->template->og_image = $articleData['og_image'];
        $this->template->og_url = $articleData['og_url'];
        $this->template->og_type = $articleData['og_type'];
        $this->template->meta_title = $articleData['meta_title'];
        $this->template->meta_description = $articleData['meta_description'];

        // ARTICLE CONTENT
        $this->template->article[] = $articleData['content'];
        $this->template->have_custom_h1 = (strpos(strtolower($articleData['content']), '<h1>') !== false);

        // DEBUG ONLY
        // bdump($articleData, "ARTICLE DATA");
    }
}
