<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Helpers\ArrayHelper;
use App\Models\Helpers\StringHelper;

class UrlGenerator extends BaseModel
{
    /**
     * Normalizes a raw category URL string into a clean array of slugs.
     *
     * Removes empty parts and validates the structure of the URL.
     *
     * @param string $categoryUrl Raw category URL (e.g. "category//sub-category/").
     * @return array<string> List of cleaned URL segments.
     * @throws CategoryException If the URL contains redundant slashes or is malformed.
     */
    public function normalizeCategoryUrl(string $categoryUrl): array
    {
        $categoryUrlListRaw = explode('/', $categoryUrl);
        $categoryUrlList = array_values(array_filter($categoryUrlListRaw));

        if (!empty($categoryUrl) && count($categoryUrlListRaw) !== count($categoryUrlList)) {
            throw new CategoryException(
                'Broken Category URL',
                \Nette\Http\IResponse::S404_NotFound
            );
        }

        return $categoryUrlList;
    }

    /**
     * Generates a full category URL path based on its ID.
     *
     * Traverses the category tree upward to construct a nested URL
     * from the root to the specified category.
     *
     * @param int $categoryId ID of the category.
     * @return string Complete category URL (e.g. "parent/sub/child/").
     * @throws CategoryException If the category or any of its parents cannot be found.
     */
    public function generateCategoryUrl(int $categoryId): string
    {
        $result = $this->db->table(CategoryManager::TABLE_NAME)
            ->order('position')
            ->fetchAll();

        $categories = ArrayHelper::resultToArray($result);

        $limit = $categories[$categoryId]['level'];
        $parent_id = $categories[$categoryId]['id'];
        $name_url = $categories[$categoryId]['name_url'];

        for ($level = 1; $level < $limit; $level++) {
            if (!isset($categories[$parent_id])) {
                throw new CategoryException(
                    "Category (ID '$parent_id') not found.",
                    \Nette\Http\IResponse::S404_NotFound
                );
            }

            $parent_id = $categories[$parent_id]['parent_id'];
            $name_url = $categories[$parent_id]['name_url'] . '/' . $name_url;
        }

        return $name_url . '/';
    }

    /**
     * Generates a full URL path for an article, including category hierarchy.
     *
     * Handles edge cases like articles in the root (main) category and the default page.
     *
     * @param int $articleId ID of the article.
     * @return string Full article URL (e.g. "category/sub/article/").
     * @throws ArticleException If the article cannot be found.
     */
    public function generateArticleUrl(int $articleId): string
    {
        $article = $this->db->table(ArticleManager::TABLE_NAME)
            ->select('name_url, category_id')
            ->where('id', $articleId)
            ->fetch();

        if (!$article) {
            throw new ArticleException(
                "Article (ID '$articleId') not found.",
                \Nette\Http\IResponse::S404_NotFound
            );
        }

        $name_url = $article['name_url'];
        $categoryId = $article['category_id'];

        if ($categoryId == CategoryManager::MAIN_CATEGORY_ID) {
            if ($this->c('DEFAULT_PAGE') == $name_url) {
                return '';
            } else {
                return $name_url . '/';
            }
        }

        try {
            $category_name_url = $this->generateCategoryUrl($categoryId);
        } catch (CategoryException $e) {
            $category_name_url = '';
        }

        return $category_name_url . $name_url . '/';
    }

    /**
     * Generates a unique slug (`name_url`) for an article.
     *
     * If the given slug already exists, appends a numeric suffix to ensure uniqueness.
     *
     * @param string $nameUrl User-provided slug suggestion.
     * @param int|null $ignoreId Optional article ID to exclude from the check (used during updates).
     * @return string Unique `name_url` suitable for saving.
     */
    public function generateUniqueNameUrl(string $nameUrl, ?int $ignoreId = null): string
    {
        $baseNameUrl = StringHelper::webalize($nameUrl);
        return $this->findUniqueNameUrl($baseNameUrl, 0, $ignoreId);
    }

    /**
     * Recursively checks the database for slug conflicts and generates a unique `name_url`.
     *
     * Adds incremental suffixes ("-1", "-2", ...) until an unused slug is found.
     *
     * @param string $baseNameUrl Base slug to check against.
     * @param int $counter Numeric suffix to try (0 means no suffix).
     * @param int|null $ignoreId Optional ID to exclude from conflict check.
     * @return string Available unique `name_url`.
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
