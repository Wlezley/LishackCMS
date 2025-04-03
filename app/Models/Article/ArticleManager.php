<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Helpers\ArrayHelper;
use App\Models\Helpers\StringHelper;
use Nette\Database\Explorer;

class ArticleManager extends BaseModel
{
    public const TABLE_NAME = 'article';

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
        $article = $this->db->table(self::TABLE_NAME)
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
        $article = $this->db->table(self::TABLE_NAME)
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
        $article = $this->db->table(self::TABLE_NAME)
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
        $categoryId = $this->db->table(self::TABLE_NAME)
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
        $newArticle = $this->db->table(self::TABLE_NAME)
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
        $this->db->table(self::TABLE_NAME)
            ->where('id', $id)
            ->update($data);
    }

    public function delete(int $id): void
    {
        $this->db->table(self::TABLE_NAME)
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
        $query = $this->db->table(self::TABLE_NAME)
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
        $query = $this->db->table(self::TABLE_NAME);

        if ($search !== null) {
            $query->where('title LIKE ? OR content LIKE ?', "%$search%", "%$search%");
        }

        return $query->count('*');
    }
}
