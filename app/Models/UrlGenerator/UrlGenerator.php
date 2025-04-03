<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Helpers\ArrayHelper;
use App\Models\Helpers\StringHelper;

class UrlGenerator extends BaseModel
{
    /** @return array<string> */
    public function normalizeCategoryUrl(string $categoryUrl): array
    {
        $categoryUrlListRaw = explode('/', $categoryUrl);
        $categoryUrlList = array_values(array_filter($categoryUrlListRaw));

        if (!empty($categoryUrl) && count($categoryUrlListRaw) !== count($categoryUrlList)) {
            throw new CategoryException('Broken Category URL', \Nette\Http\IResponse::S404_NotFound);
        }

        return $categoryUrlList;
    }

    public function generateCategoryUrl(int $id): string
    {
        $result = $this->db->table(CategoryManager::TABLE_NAME)
            ->order('position')
            ->fetchAll();

        $categories = ArrayHelper::resultToArray($result);

        $limit = $categories[$id]['level'];
        $parent_id = $categories[$id]['id'];
        $name_url = $categories[$id]['name_url'];

        for ($level = 1; $level < $limit; $level++) {
            if (!isset($categories[$parent_id])) {
                throw new CategoryException("Category ID $parent_id not found.", 1);
            }

            $parent_id = $categories[$parent_id]['parent_id'];
            $name_url = $categories[$parent_id]['name_url'] . '/' . $name_url;
        }

        return $name_url . '/';
    }

    public function generateArticleUrl(int $id): string
    {
        $article = $this->db->table(ArticleManager::TABLE_NAME)
            ->select('name_url, category_id')
            ->where('id', $id)
            ->fetch();

        if (!$article) {
            throw new ArticleException("Article ID $id not found.", 1);
        }

        $name_url = $article['name_url'];
        $cID = $article['category_id'];

        if ($cID == CategoryManager::MAIN_CATEGORY_ID) {
            if ($this->c('DEFAULT_PAGE') == $name_url) {
                return '';
            } else {
                return $name_url . '/';
            }
        }

        try {
            $category_name_url = $this->generateCategoryUrl($cID);
        } catch (CategoryException $e) {
            $category_name_url = '';
        }

        return $category_name_url . $name_url . '/';
    }

    /**
     * Generates a unique `name_url` for an article by appending a numerical suffix if needed.
     *
     * @param string $nameUrl The proposed `name_url` from the user.
     * @param int|null $ignoreId If updating an existing article, provide its ID to ignore in the uniqueness check.
     * @return string Unique `name_url` that does not exist in the database.
     */
    public function generateUniqueNameUrl(string $nameUrl, ?int $ignoreId = null): string
    {
        $baseNameUrl = StringHelper::webalize($nameUrl);
        return $this->findUniqueNameUrl($baseNameUrl, 0, $ignoreId);
    }

    /**
     * Recursively finds a unique `name_url` by checking the database and adding a numerical suffix if necessary.
     *
     * @param string $baseNameUrl The base `name_url` (without suffix).
     * @param int $counter The current numerical suffix (0 means no suffix).
     * @param int|null $ignoreId If updating, this ID is ignored in the uniqueness check.
     * @return string Unique `name_url`.
     */
    private function findUniqueNameUrl(string $baseNameUrl, int $counter, ?int $ignoreId): string
    {
        $newNameUrl = $counter > 0 ? "{$baseNameUrl}-{$counter}" : $baseNameUrl;

        $query = $this->db->table(ArticleManager::TABLE_NAME)
            ->where('name_url', $newNameUrl);

        if ($ignoreId !== null) {
            $query->where('id != ?', $ignoreId);
        }

        $exists = $query->fetch();

        return $exists ? $this->findUniqueNameUrl($baseNameUrl, $counter + 1, $ignoreId) : $newNameUrl;
    }
}
