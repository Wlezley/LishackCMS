<?php

declare(strict_types=1);

namespace App\Modules\Website\Presenters;

use App\Models\ArticleException;
use App\Models\ArticleManager;
use App\Models\CategoryException;
use App\Models\CategoryManager;
use App\Models\UrlGenerator;
use Nette;

final class ArticlePresenter extends BasePresenter
{
    /** @var ArticleManager @inject */
    public ArticleManager $articleManager;

    /** @var CategoryManager @inject */
    public CategoryManager $categoryManager;

    /** @var UrlGenerator @inject */
    public UrlGenerator $urlGenerator;

    /** @var array<string,mixed> $article */
    private array $article;

    private string $titlePrefix = '';
    private string $titleSuffix = '';

    private bool $isCategory = false;
    private ?int $activeCategory = null;

    public function actionDefault(string $articleUrl = '', string $categoryUrl = ''): void
    {
        try {
            $categoryUrlList = $this->urlGenerator->normalizeCategoryUrl($categoryUrl);
        } catch (CategoryException $e) {
            $this->article = $this->articleManager->getByNameUrl('404');
            $this->getHttpResponse()->setCode(Nette\Http\IResponse::S404_NotFound);
            return;
        }

        if ($articleUrl === $this->c('DEFAULT_PAGE')) {
            $this->article = $this->articleManager->getByNameUrl('404');
            $this->getHttpResponse()->setCode(Nette\Http\IResponse::S404_NotFound);
            return;
        }

        $articleUrl = $articleUrl ?: $this->c('DEFAULT_PAGE');

        try {
            $categoryId = $this->categoryManager->resolveCategoryId($categoryUrlList);
            $articleId = $this->articleManager->getIdByUrlAndCategory($articleUrl, $categoryId);
            $this->article = $this->articleManager->getById($articleId);
            $this->activeCategory = $categoryId;
        } catch (\Exception $e) {
            try {
                $categoryUrlList[] = $articleUrl;
                $categoryId = $this->categoryManager->resolveCategoryId($categoryUrlList);
                $this->isCategory = true;
                $this->activeCategory = $categoryId;
                $this->titlePrefix = 'Kategorie: ';
            } catch (CategoryException $e) {
                $this->article = $this->articleManager->getByNameUrl('404');
                $this->getHttpResponse()->setCode(Nette\Http\IResponse::S404_NotFound);
                return;
            }
        }

        if (!$this->isCategory && $this->article['published'] != 1) {
            if ($this->user->isLoggedIn() && $this->getHttpRequest()->getQuery('preview') == 1) {
                $this->titlePrefix = '[ID: ' . $this->article['id'] . '] ';
                $this->titleSuffix = ' ' . $this->t('article.title.preview');
            } else {
                $this->article = $this->articleManager->getByNameUrl('404');
                $this->getHttpResponse()->setCode(Nette\Http\IResponse::S404_NotFound);
                return;
            }
        }
    }

    public function renderDefault(): void
    {
        if ($this->isCategory) {
            $category = $this->categoryManager->getById($this->activeCategory);
            $this->template->title = $this->titlePrefix . $category['name'] . $this->titleSuffix;

            $this->template->categoryUrl = HOME_URL . $this->urlGenerator->generateCategoryUrl($this->activeCategory);
            $this->template->articleList = $this->articleManager->getList(50, 0, null, $this->activeCategory);
            $this->template->activeCategory = $this->activeCategory;

            $this->template->adminUrl = $this->template->adminUrl . "menu/edit?id={$category['id']}";

            $this->template->setFile(__DIR__ . '/../Templates/Article/list.latte');
        } else {
            // PAGE TITLE
            $this->template->title = $this->titlePrefix . $this->article['title'] . $this->titleSuffix;

            // SEO
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

            // ARTICLE CONTENT, MENU
            $this->template->article[] = $this->article['content'];
            $this->template->have_custom_h1 = (strpos(strtolower($this->article['content']), '<h1') !== false);
            $this->template->activeCategory = $this->activeCategory;

            // ADMIN URL
            $this->template->adminUrl = $this->template->adminUrl . "article/edit?id={$this->article['id']}";

            $this->template->setFile(__DIR__ . '/../Templates/Article/detail.latte');

            // DEBUG ONLY
            // bdump($this->article, "ARTICLE DATA");
            // bdump($urlScript, "URL SCRIPT");
        }
    }
}
