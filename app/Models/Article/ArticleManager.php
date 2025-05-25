<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Helpers\ArrayHelper;
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

    /**
     * Retrieves an article by its unique ID.
     *
     * @param int $id ID of the article.
     * @return array<string,mixed> Article data as an associative array.
     * @throws ArticleException If the article does not exist.
     */
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

    /**
     * Retrieves an article by its unique `name_url` slug.
     *
     * @param string $name_url Slug of the article.
     * @return array<string,mixed> Article data as an associative array.
     * @throws ArticleException If the article is not found.
     */
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

    /**
     * Finds the article ID based on its unique `name_url` slug and category ID.
     *
     * @param string $nameUrl Slug of the article.
     * @param int $categoryId Category ID the article belongs to.
     * @return int ID of the matching article.
     * @throws ArticleException If no matching article is found.
     */
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

    /**
     * Gets the category ID associated with a given article ID.
     *
     * @param int $id ID of the article.
     * @return int Category ID of the article.
     * @throws ArticleException If the article does not exist or has no category.
     */
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
     * Inserts a new article into the database.
     *
     * @param array<string,mixed> $data Article data to insert.
     * @return int ID of the newly created article.
     * @throws ArticleException If the creation fails.
     *
     * @todo Create something like... ArticleValidator::prepare($data)
     */
    public function create(array $data): int
    {
        $newArticle = $this->db->table(self::TABLE_NAME)
            ->insert($data);

        if (!$newArticle) {
            throw new ArticleException(
                'Article creation failed.',
                \Nette\Http\IResponse::S500_InternalServerError
            );
        }

        return (int) $newArticle['id'];
    }

    /**
     * Updates an existing article by its ID.
     *
     * @param int $id ID of the article to update.
     * @param array<string,mixed> $data Associative array of data to update.
     * @throws ArticleException If the article does not exist.
     *
     * @todo Create something like... ArticleValidator::prepare($data)
     */
    public function update(int $id, array $data): void
    {
        if (isset($data['id'])) {
            unset($data['id']);
        }

        $query = $this->db->table(self::TABLE_NAME)
            ->where('id', $id);

        if (!$query->fetch()) {
            throw new ArticleException(
                "Article (ID '$id') not found.",
                \Nette\Http\IResponse::S404_NotFound
            );
        }

        $query->update($data);
    }

    /**
     * Deletes an article by its ID.
     *
     * @param int $id ID of the article to delete.
     */
    public function delete(int $id): void
    {
        $this->db->table(self::TABLE_NAME)
            ->where('id', $id)
            ->delete();
    }

    /**
     * Updates the category ID of all articles that belong to the given old category.
     *
     * @param int $oldCategoryId The current category ID to replace.
     * @param int $newCategoryId The new category ID to assign.
     */
    public function updateCategoryId(int $oldCategoryId, int $newCategoryId): void
    {
        $this->db->table(self::TABLE_NAME)
            ->where('category_id', $oldCategoryId)
            ->update(['category_id' => $newCategoryId]);
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
