<?php

declare(strict_types=1);

namespace App\Modules\Website\Presenters;

use App\Models\ArticleException;
use App\Models\ArticleManager;
use Nette;

final class ArticlePresenter extends BasePresenter
{
    /** @var ArticleManager @inject */
    public ArticleManager $articleManager;

    /** @todo Translate error messages */
    public function renderDetail(string $articleUrl = '', string $categoryUrl = ''): void
    {
        try {
            $categoryUrlList = $this->articleManager->normalizeCategoryUrl($categoryUrl);
        } catch (ArticleException $e) {
            $this->redirect('this', [
                'articleUrl' => $articleUrl,
                'categoryUrl' => $e->getCategoryUrl(),
            ]);
        }

        if (empty($categoryUrlList)) {
            if ($articleUrl === $this->c('DEFAULT_PAGE')) {
                $this->redirect('this', ['articleUrl' => '']);
            }

            if ($articleUrl == '404') {
                $this->getHttpResponse()->setCode(Nette\Http\IResponse::S404_NotFound);
            }
        }

        $articleUrl = $articleUrl ?: $this->c('DEFAULT_PAGE');

        try {
            $categoryId = $this->articleManager->resolveCategoryId($categoryUrlList);
            $articleId = $this->articleManager->getIdByUrlAndCategory($articleUrl, $categoryId);
            $article = $this->articleManager->getById($articleId);
        } catch (ArticleException $e) {
            $this->error($e->getMessage(), $e->getCode());
            return;
        }

        if ($article['published'] != 1) {
            $this->error('Článek nebyl publikován.', Nette\Http\IResponse::S404_NotFound);
        }

        // ARTICLE ATTRIBUTES
        $this->template->title = $article['title'];
        $this->template->robots = $article['robots'];
        $this->template->canonical_url = $article['canonical_url'];
        $this->template->og_title = $article['og_title'];
        $this->template->og_description = $article['og_description'];
        $this->template->og_image = $article['og_image'];
        $this->template->og_url = $article['og_url'];
        $this->template->og_type = $article['og_type'];
        $this->template->meta_title = $article['meta_title'];
        $this->template->meta_description = $article['meta_description'];

        // ARTICLE CONTENT
        $this->template->article[] = $article['content'];
        $this->template->have_custom_h1 = (strpos(strtolower($article['content']), '<h1>') !== false);

        // DEBUG ONLY
        // bdump($article, "ARTICLE DATA");
    }
}
