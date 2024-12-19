<?php

declare(strict_types=1);

namespace App\Modules\Website\Presenters;

use Nette;

final class ArticlePresenter extends BasePresenter
{
    // public function __construct(
    // ) {}

    public function renderDetail(string $articleUrl = '', string $categoryUrl = ''): void
    {
        // INPUT DEBUG
        bdump([
            'art_url' => $articleUrl,
            'cat_url' => $categoryUrl,
            'cat_list' => explode('/', $categoryUrl),
            'cat_list_filter' => array_filter(explode('/', $categoryUrl)),
            'cat_list_filter_values' => array_values(array_filter(explode('/', $categoryUrl))),
            'cat_url_fixed' => implode('/', array_values(array_filter(explode('/', $categoryUrl)))),
        ], "ARTICLE DETAIL SEARCH");

        // Category URL parse
        $categoryListRaw = explode('/', $categoryUrl);
        $categoryList = array_values(array_filter($categoryListRaw));

        // Broken Category URL will redirect to a fixed URL (maybe just trigger error 404?)
        if (!empty($categoryUrl) && count($categoryListRaw) !== count($categoryList)) {
            $this->redirect('this', [
                'articleUrl' => $articleUrl,
                'categoryUrl' => implode('/', $categoryList),
            ]);
        }

        // No Category
        if (empty($categoryList)) {
            // Homepage is always without articleUrl
            if ($articleUrl === DEFAULT_PAGE) {
                $this->redirect('this', []);
            }

            // Error 404 page
            if ($articleUrl == '404') {
                $this->getHttpResponse()->setCode(Nette\Http\IResponse::S404_NotFound);
            }
        }

        // Homepage
        if (empty($articleUrl)) {
            // Redirect unwanted behavior (maybe just trigger error 404?)
            if (!empty($categoryList)) {
                $this->redirect('this', []);
            }

            $articleUrl = DEFAULT_PAGE;
        }

        // // Load Category & trace Article
        // if (!empty($categoryList)) {

        // } else {
        //     // Article without category
        //     $article = $this->db->table('article')
        //         ->where([
        //             'name_url' => $articleUrl,
        //         ])
        //         ->fetch();
        // }

        // Load Article
        $article = $this->db->table('article')
            ->where('name_url', $articleUrl)
            ->fetch();

        bdump($article, "DB: ARTICLE");

        if (!$article) {
            $this->error('Článek nebyl nalezen.', Nette\Http\IResponse::S404_NotFound);
        }

        // Kategorie
        if (!empty($categoryList)) {
            // Načti kategorie článku seřazené podle pořadí
            $categories = $this->db->table('article_category')
                ->where('article_id', $article->id) // @phpstan-ignore property.notFound
                ->order('order')
                ->fetchPairs('order', 'menu_id');

            bdump($categories, "article_category");

            if (!$categories) {
                $this->error('Kategorie nebyla nalezena.', Nette\Http\IResponse::S404_NotFound);
            }

            // Načti názvy kategorií
            $categoryUrls = $this->db->table('menu')
                ->where('id', $categories)
                ->order('FIELD(id, ?)', array_values($categories)) // zachová pořadí podle `article_category`
                ->fetchPairs('id', 'name_url');

            // Validace URL
            // $expectedUrl = implode('/', $categoryUrls);
            // if ($expectedUrl !== "{$categoryUrl}/{$subCategoryUrl}") {
            //     $this->redirectUrl("/{$expectedUrl}/{$article->name_url}/");
            // }
        }

        $this->title = $article->title; // @phpstan-ignore property.notFound
        // $this->robots = $article->robots;
        // $this->canonical_url = $article->canonical_url;
        // $this->og_title = $article->og_title;
        // $this->og_description = $article->og_description;
        // $this->og_image = $article->og_image;
        // $this->og_url = $article->og_url;
        // $this->og_type = $article->og_type;
        // $this->meta_title = $article->meta_title;
        // $this->meta_description = $article->meta_description;

        $this->template->article[] = $article->content; // @phpstan-ignore property.notFound

        bdump($article->toArray(), "ARTICLE DATA");

        // $this->template->categories = $categoryUrls;
    }
}
