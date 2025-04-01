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

    /** @var array<string,mixed> $article */
    private array $article;

    public function actionDetail(string $articleUrl = '', string $categoryUrl = ''): void
    {
        try {
            $categoryUrlList = $this->articleManager->normalizeCategoryUrl($categoryUrl);
        } catch (ArticleException $e) {
            $this->article = $this->articleManager->getByNameUrl('404');
            $this->getHttpResponse()->setCode(Nette\Http\IResponse::S404_NotFound);
            return;
        }

        if (empty($categoryUrlList)) {
            if ($articleUrl === $this->c('DEFAULT_PAGE')) {
                $this->redirect('this', ['articleUrl' => '']);
            }

            if ($articleUrl == '404') {
                $this->article = $this->articleManager->getByNameUrl('404');
                $this->getHttpResponse()->setCode(Nette\Http\IResponse::S404_NotFound);
                return;
            }
        }

        $articleUrl = $articleUrl ?: $this->c('DEFAULT_PAGE');

        try {
            $categoryId = $this->articleManager->resolveCategoryId($categoryUrlList);
            $articleId = $this->articleManager->getIdByUrlAndCategory($articleUrl, $categoryId);
            $this->article = $this->articleManager->getById($articleId);
        } catch (ArticleException $e) {
            if ($e->getCode() == Nette\Http\IResponse::S404_NotFound) {
                $this->article = $this->articleManager->getByNameUrl('404');
                $this->getHttpResponse()->setCode(Nette\Http\IResponse::S404_NotFound);
            } else {
                $this->error($e->getMessage(), $e->getCode());
            }
            return;
        }
    }

    public function renderDetail(): void
    {
        $titleSuffix = '';
        if ($this->article['published'] != 1) {
            if ($this->user->isLoggedIn() && $this->getHttpRequest()->getQuery('preview') == 1) {
                $titleSuffix .= ' ' . $this->t('article.title.preview');
            } else {
                $this->article = $this->articleManager->getByNameUrl('404');
                $this->getHttpResponse()->setCode(Nette\Http\IResponse::S404_NotFound);
            }
        }

        // PAGE TITLE
        $this->template->title = $this->article['title'] . $titleSuffix;

        // ARTICLE ATTRIBUTES
        $urlScript = $this->getHttpRequest()->getUrl();
        $this->template->seo_index = $this->article['robots'] ?: $this->c('SEO_ROBOTS');
        $this->template->seo_canonical = $this->article['canonical_url'] ?: $urlScript->getHostUrl() . $urlScript->getPath();
        $this->template->seo_title = $this->article['meta_title'] ?: $this->article['title'];
        $this->template->seo_description = $this->article['meta_description'] ?: $this->c('SEO_DESCRIPTION');

        // OPEN GRAPH PROTOCOL
        $this->template->og_title = $this->article['og_title'] ?: $this->template->seo_title;
        $this->template->og_description = $this->article['og_description'] ?: $this->template->seo_description;
        $this->template->og_image = $this->article['og_image'] ?: $this->c('OG_IMAGE');
        $this->template->og_url = $this->article['og_url'] ?: $this->template->seo_canonical;
        $this->template->og_type = $this->article['og_type'] ?: 'website';

        // ARTICLE CONTENT
        $this->template->article[] = $this->article['content'];
        $this->template->have_custom_h1 = (strpos(strtolower($this->article['content']), '<h1') !== false);

        // DEBUG ONLY
        // bdump($article, "ARTICLE DATA");
        // bdump($urlScript, "URL SCRIPT");
    }
}
