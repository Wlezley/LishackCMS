<?php

declare(strict_types=1);

namespace App\Models\UrlGenerator;

use App\Exception\ArticleException;
use App\Exception\CategoryException;
use App\Models\Article\ArticleManager;
use App\Models\Category\CategoryManager;
use App\Models\Config\ConfigManager;
use App\Models\Helpers\ArrayHelper;
use App\Models\Helpers\StringHelper;
use Nette\Database\Explorer;

class UrlGenerator
{
    use \App\Models\Config\Config;

    public function __construct(
        protected Explorer $db,
        protected ConfigManager $configManager
    ) {
    }

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
        $parentId = $categories[$categoryId]['id'];
        $nameUrl = $categories[$categoryId]['name_url'];

        for ($level = 1; $level < $limit; $level++) {
            if (!isset($categories[$parentId])) {
                throw new CategoryException(
                    "Category (ID '$parentId') not found.",
                    \Nette\Http\IResponse::S404_NotFound
                );
            }

            $parentId = $categories[$parentId]['parent_id'];
            $nameUrl = $categories[$parentId]['name_url'] . '/' . $nameUrl;
        }

        return $nameUrl . '/';
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

        $nameUrl = $article['name_url'];
        $categoryId = $article['category_id'];

        if ($categoryId == CategoryManager::MAIN_CATEGORY_ID) {
            if ($this->c('DEFAULT_PAGE') == $nameUrl) {
                return '';
            } else {
                return $nameUrl . '/';
            }
        }

        try {
            $categoryNameUrl = $this->generateCategoryUrl($categoryId);
        } catch (CategoryException $e) {
            $categoryNameUrl = '';
        }

        return $categoryNameUrl . $nameUrl . '/';
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
