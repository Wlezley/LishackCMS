<?php

declare(strict_types=1);

namespace App\Modules\Website\Presenters;

use Nette;

final class ArticlePresenter extends BasePresenter
{
    public const MAIN_CATEGORY_ID = 1;

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
        $articleCategoryId = self::MAIN_CATEGORY_ID;

        if (!empty($categoryUrlList)) {
            $categoryResult = $this->db->table('category')
                ->select('id, parent_id, name_url')
                ->fetchAll();

            foreach ($categoryUrlList as $categoryUrlName) {
                $found = false;

                foreach ($categoryResult as $categoryRow) {
                    if ($categoryRow['name_url'] == $categoryUrlName && $categoryRow['parent_id'] == $articleCategoryId) {
                        $found = true;
                        $articleCategoryId = $categoryRow['id'];
                        break;
                    }
                }

                if (!$found) {
                    $articleCategoryId = null;
                    break;
                }
            }

            if (!$articleCategoryId) {
                $this->error('Článek nebyl v kategoriích nalezen.', Nette\Http\IResponse::S404_NotFound);
            }
        }

        // GET ARTICLE ID
        $articleId = $this->db->table('article_category')
            ->select('article_id')
            ->where('article_name_url', $articleUrl)
            ->where('category_id', $articleCategoryId)
            ->fetch();

        if (!$articleId) {
            bdump($articleCategoryId, "ARTICLE CATEGORY ID");
            $this->error('ID Článku nebylo nalezeno.', Nette\Http\IResponse::S404_NotFound);
        }

        $articleId = $articleId['article_id'];

        // GET ARTICLE DATA
        $articleData = $this->db->table('article')
            ->where('id', $articleId)
            ->fetch();

        if (!$articleData) {
            bdump($articleId, "ARTICLE ID");
            $this->error('Článek nebyl nalezen.', Nette\Http\IResponse::S404_NotFound);
        }

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

        // bdump($articleData, "ARTICLE DATA");
    }
}
