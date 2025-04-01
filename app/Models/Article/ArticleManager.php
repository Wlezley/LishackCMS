<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Helpers\ArrayHelper;
use App\Models\Helpers\StringHelper;
use Nette\Database\Explorer;

class ArticleManager extends BaseModel
{
    public const TABLE_NAME_ARTICLE = 'article';

    public function __construct(
        protected Explorer $db,
        protected ConfigManager $configManager,
        protected TranslationManager $translationManager,
        public CategoryManager $categoryManager
    ) {
        parent::__construct($db, $configManager, $translationManager);
    }

    /** @return array<string,mixed> */
    public function getById(int $id): array
    {
        $article = $this->db->table(self::TABLE_NAME_ARTICLE)
            ->where('id', $id)
            ->fetch();

        if (!$article) {
            throw new ArticleException(
                "Article (ID '$id') not found.",
                \Nette\Http\IResponse::S404_NotFound
            );
        }

        return $article->toArray();
    }

    /** @return array<string,mixed> */
    public function getByNameUrl(string $name_url): array
    {
        $article = $this->db->table(self::TABLE_NAME_ARTICLE)
            ->where('name_url', $name_url)
            ->fetch();

        if (!$article) {
            throw new ArticleException(
                "Article (name_url: '$name_url') not found.",
                \Nette\Http\IResponse::S404_NotFound
            );
        }

        return $article->toArray();
    }

    public function getIdByUrlAndCategory(string $nameUrl, int $categoryId): int
    {
        $article = $this->db->table(self::TABLE_NAME_ARTICLE)
            ->select('id')
            ->where('name_url', $nameUrl)
            ->where('category_id', $categoryId)
            ->fetch();

        if (!$article) {
            throw new ArticleException(
                "Unable to find article ID by name_url: '$nameUrl' and category_id: '$categoryId'.",
                \Nette\Http\IResponse::S404_NotFound
            );
        }

        return (int) $article['id'];
    }

    public function getCategoryIdById(int $id): int
    {
        $categoryId = $this->db->table(self::TABLE_NAME_ARTICLE)
            ->select('category_id')
            ->where('id', $id)
            ->fetch();

        if (!$categoryId) {
            throw new ArticleException(
                "Unable to find category ID by article ID: '$id'.",
                \Nette\Http\IResponse::S404_NotFound
            );
        }

        return (int) $categoryId['category_id'];
    }

    public function generateUrl(int $id): string
    {
        $article = $this->db->table(self::TABLE_NAME_ARTICLE)
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
            $category_name_url = $this->categoryManager->generateUrl($cID);
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

        $query = $this->db->table(self::TABLE_NAME_ARTICLE)
            ->where('name_url', $newNameUrl);

        if ($ignoreId !== null) {
            $query->where('id != ?', $ignoreId);
        }

        $exists = $query->fetch();

        return $exists ? $this->findUniqueNameUrl($baseNameUrl, $counter + 1, $ignoreId) : $newNameUrl;
    }

    // #####################################
    // ###          DB HANDLERS          ###
    // #####################################

    /**
     * @param array<string,mixed> $data Article data
     * @return int New article ID
     * @throws ArticleException If article cration failed.
     *
     * @todo Create something like... ArticleValidator::prepare($data)
     */
    public function create(array $data): int
    {
        $newArticle = $this->db->table(self::TABLE_NAME_ARTICLE)
            ->insert($data);

        if (!$newArticle) {
            throw new ArticleException('Article creation failed.', 1);
        }

        return (int) $newArticle['id'];
    }

    /**
     * @param int $id Article ID
     * @param array<string,mixed> $data Article data
     *
     * @todo Create something like... ArticleValidator::prepare($data)
     */
    public function update(int $id, array $data): void
    {
        $this->db->table(self::TABLE_NAME_ARTICLE)
            ->where('id', $id)
            ->update($data);
    }

    public function delete(int $id): void
    {
        $this->db->table(self::TABLE_NAME_ARTICLE)
            ->where('id', $id)
            ->delete();
    }

    // #####################################
    // ###         ARTICLE LIST          ###
    // #####################################

    /**
     * Retrieves a list of articles with optional search and pagination.
     *
     * @param int $limit Number of results to return (default: 50).
     * @param int $offset Offset for pagination (default: 0).
     * @param string|null $search Optional search query for article title and content.
     * @param int $categoryId Category ID filter.
     * @return array<int|string,array<string,string|int|null>>|null Array of articles indexed by id, or null if empty.
     */
    public function getList(int $limit = 50, int $offset = 0, ?string $search = null, ?int $categoryId = null): ?array
    {
        $query = $this->db->table(self::TABLE_NAME_ARTICLE)
            ->limit($limit, $offset)
            ->order('id ASC');

        if ($search !== null) {
            $query->where('title LIKE ? OR content LIKE ?', "%$search%", "%$search%");
        }

        if ($categoryId !== null) {
            $query->where('category_id ?', $categoryId);
        }

        $data = $query->fetchAll();

        return $data ? ArrayHelper::resultToArray($data) : null;
    }

    /**
     * Gets the total count of articles, optionally filtered by a search query.
     *
     * @param string|null $search Optional search query for article title and content.
     * @return int Total count of matching articles.
     */
    public function getCount(?string $search = null): int
    {
        $query = $this->db->table(self::TABLE_NAME_ARTICLE);

        if ($search !== null) {
            $query->where('title LIKE ? OR content LIKE ?', "%$search%", "%$search%");
        }

        return $query->count('*');
    }
}
