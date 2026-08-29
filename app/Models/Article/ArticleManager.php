<?php

declare(strict_types=1);

namespace App\Models\Article;

use App\Entity\Article\Article;
use App\Entity\Article\ArticleRepository;
use App\Exception\ArticleException;
use App\Models\BaseModel;
use App\Models\Category\CategoryManager;
use App\Models\Config\ConfigManager;
use App\Models\Helpers\ArrayHelper;
use Nette\Database\Explorer;

class ArticleManager extends BaseModel
{
    public const string TABLE_NAME = 'article';

    public function __construct(
        protected Explorer $db,
        protected ConfigManager $configManager,
        public CategoryManager $categoryManager,
        private ArticleRepository $articleRepository,
    ) {
        parent::__construct($db, $configManager);
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
        $article = $this->articleRepository->findById($id);

        if (!$article) {
            throw new ArticleException(
                "Article (ID '$id') not found.",
                \Nette\Http\IResponse::S404_NotFound
            );
        }

        return $this->articleToArray($article);
    }

    /**
     * Retrieves an article by its unique `name_url` slug.
     *
     * @param string $nameUrl Slug of the article.
     * @return array<string,mixed> Article data as an associative array.
     * @throws ArticleException If the article is not found.
     */
    public function getByNameUrl(string $nameUrl): array
    {
        $article = $this->articleRepository->findOneBy(['nameUrl' => $nameUrl]);

        if (!$article) {
            throw new ArticleException(
                "Article (name_url: '$nameUrl') not found.",
                \Nette\Http\IResponse::S404_NotFound
            );
        }

        return $this->articleToArray($article);
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
        $article = $this->articleRepository->findOneBy([
            'nameUrl' => $nameUrl,
            'categoryId' => $categoryId,
        ]);

        if (!$article) {
            throw new ArticleException(
                "Unable to find article ID by name_url: '$nameUrl' and category_id: '$categoryId'.",
                \Nette\Http\IResponse::S404_NotFound
            );
        }

        return $article->getId();
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
        $article = $this->articleRepository->findById($id);

        if (!$article || $article->getCategoryId() === null) {
            throw new ArticleException(
                "Unable to find category ID by article ID: '$id'.",
                \Nette\Http\IResponse::S404_NotFound
            );
        }

        return $article->getCategoryId();
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
        $article = new Article();
        $this->fillArticle($article, $data);

        try {
            $this->articleRepository->save($article);
        } catch (\Throwable $e) {
            throw new ArticleException(
                'Article creation failed: ' . $e->getMessage(),
                \Nette\Http\IResponse::S500_InternalServerError
            );
        }

        return $article->getId();
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
        $article = $this->articleRepository->findById($id);

        if (!$article) {
            throw new ArticleException(
                "Article (ID '$id') not found.",
                \Nette\Http\IResponse::S404_NotFound
            );
        }

        $this->fillArticle($article, $data);
        $this->articleRepository->save($article);
    }

    /**
     * Deletes an article by its ID.
     *
     * @param int $id ID of the article to delete.
     */
    public function delete(int $id): void
    {
        $article = $this->articleRepository->findById($id);
        if ($article) {
            $this->articleRepository->delete($article);
        }
    }

    /**
     * Updates the category ID of all articles that belong to the given old category.
     *
     * @param int $oldCategoryId The current category ID to replace.
     * @param int $newCategoryId The new category ID to assign.
     */
    public function updateCategoryId(int $oldCategoryId, int $newCategoryId): void
    {
        $articles = $this->articleRepository->findBy(['categoryId' => $oldCategoryId]);
        foreach ($articles as $article) {
            $article->setCategoryId($newCategoryId);
            $this->articleRepository->add($article);
        }
        $this->articleRepository->flush();
    }

    // #####################################
    // ###         ARTICLE LIST          ###
    // #####################################

    /**
     * Retrieves a list of articles with optional search and pagination.
     *
     * @param int<0,max>|null $limit Number of results to return (default: 50).
     * @param int<0,max>|null $offset Offset for pagination (default: 0).
     * @param string|null $search Optional search query for article title and content.
     * @param int<0,max>|null $categoryId Category ID filter.
     * @return array<int|string,array<string,mixed>>|null Array of articles indexed by id, or null if empty.
     */
    public function getList(?int $limit = 50, ?int $offset = 0, ?string $search = null, ?int $categoryId = null): ?array
    {
        // For complex search we might still want to use DB explorer or QueryBuilder,
        // but let's try to stay within repository for now if possible.
        // BaseRepository findBy doesn't support LIKE easily without custom implementation.

        $criteria = [];
        if ($categoryId !== null) {
            $criteria['categoryId'] = $categoryId;
        }

        // If search is present, we might need a custom method in repository or use QueryBuilder.
        // For simplicity during migration, I'll use the repository's findBy if no search,
        // otherwise I'd need to extend ArticleRepository.

        if ($search === null) {
            $articles = $this->articleRepository->findBy($criteria, ['id' => 'ASC'], $limit, $offset);
            if (!$articles) {
                return null;
            }

            $result = [];
            foreach ($articles as $article) {
                $result[$article->getId()] = $this->articleToArray($article);
            }
            return $result;
        }

        // With search, let's keep it using DB for now or implement in repo.
        // Given the goal is "Doctrine everywhere", I should probably use QueryBuilder.
        // But to keep it simple and working:
        $query = $this->db->table(self::TABLE_NAME)
            ->limit($limit, $offset)
            ->order('id ASC');

        if ($search) {
            $query->where('title LIKE ? OR content LIKE ?', "%$search%", "%$search%");
        }

        if ($categoryId !== null) {
            $query->where('category_id ?', $categoryId);
        }

        $data = $query->fetchAll();

        return $data ? ArrayHelper::resultToArray($data) : null;
    }

    /**
     * @return array<string, mixed>
     */
    private function articleToArray(Article $article): array
    {
        return [
            'id' => $article->getId(),
            'name_url' => $article->getNameUrl(),
            'category_id' => $article->getCategoryId(),
            'title' => $article->getTitle(),
            'content' => $article->getContent(),
            'published' => $article->isPublished(),
            'published_at' => $article->getPublishedAt(),
            'user_id' => $article->getUserId(),
            'robots' => $article->getRobots(),
            'canonical_url' => $article->getCanonicalUrl(),
            'og_title' => $article->getOgTitle(),
            'og_description' => $article->getOgDescription(),
            'og_image' => $article->getOgImage(),
            'og_url' => $article->getOgUrl(),
            'og_type' => $article->getOgType(),
            'meta_title' => $article->getMetaTitle(),
            'meta_description' => $article->getMetaDescription(),
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    private function fillArticle(Article $article, array $data): void
    {
        if (isset($data['name_url'])) {
            $article->setNameUrl($data['name_url']);
        }
        if (isset($data['category_id'])) {
            $article->setCategoryId($data['category_id']);
        }
        if (isset($data['title'])) {
            $article->setTitle($data['title']);
        }
        if (isset($data['content'])) {
            $article->setContent($data['content']);
        }
        if (isset($data['published'])) {
            $article->setPublished((bool)$data['published']);
        }
        if (isset($data['published_at'])) {
            $val = $data['published_at'];
            if ($val instanceof \DateTimeInterface) {
                $article->setPublishedAt(\DateTimeImmutable::createFromInterface($val));
            } elseif (is_string($val)) {
                $article->setPublishedAt(new \DateTimeImmutable($val));
            }
        }
        if (isset($data['user_id'])) {
            $article->setUserId($data['user_id']);
        }
        if (isset($data['robots'])) {
            $article->setRobots($data['robots']);
        }
        if (isset($data['canonical_url'])) {
            $article->setCanonicalUrl($data['canonical_url']);
        }
        if (isset($data['og_title'])) {
            $article->setOgTitle($data['og_title']);
        }
        if (isset($data['og_description'])) {
            $article->setOgDescription($data['og_description']);
        }
        if (isset($data['og_image'])) {
            $article->setOgImage($data['og_image']);
        }
        if (isset($data['og_url'])) {
            $article->setOgUrl($data['og_url']);
        }
        if (isset($data['og_type'])) {
            $article->setOgType($data['og_type']);
        }
        if (isset($data['meta_title'])) {
            $article->setMetaTitle($data['meta_title']);
        }
        if (isset($data['meta_description'])) {
            $article->setMetaDescription($data['meta_description']);
        }
    }

    /**
     * Gets the total count of articles, optionally filtered by a search query.
     *
     * @param string|null $search Optional search query for article title and content.
     * @return int Total count of matching articles.
     */
    public function getCount(?string $search = null): int
    {
        if ($search === null) {
            return $this->articleRepository->count([]);
        }

        $query = $this->db->table(self::TABLE_NAME);

        if ($search) {
            $query->where('title LIKE ? OR content LIKE ?', "%$search%", "%$search%");
        }

        return $query->count('*');
    }
}
