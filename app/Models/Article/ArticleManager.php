<?php

declare(strict_types=1);

namespace App\Models\Article;

use App\Entity\Article\Article;
use App\Entity\Article\ArticleRepositoryInterface;
use App\Entity\Category\CategoryRepositoryInterface;
use App\Entity\Seo\SeoRepositoryInterface;
use App\Entity\User\UserRepositoryInterface;
use App\Exception\ArticleException;
use App\Models\BaseModel;
use App\Models\Category\CategoryManager;
use App\Models\Config\ConfigManager;

class ArticleManager extends BaseModel
{
    public const string TABLE_NAME = 'article';

    public function __construct(
        protected ConfigManager $configManager,
        public CategoryManager $categoryManager,
        private ArticleRepositoryInterface $articleRepository,
        private CategoryRepositoryInterface $categoryRepository,
        private UserRepositoryInterface $userRepository,
        private SeoRepositoryInterface $seoRepository,
    ) {
        parent::__construct($configManager);
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
            'category' => $categoryId,
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

        if (!$article || $article->getCategory()?->getId() === null) {
            throw new ArticleException(
                "Unable to find category ID by article ID: '$id'.",
                \Nette\Http\IResponse::S404_NotFound
            );
        }

        return $article->getCategory()?->getId() ?: 0;
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
        $article = new Article(
            title: '',
            nameUrl: '',
            category: null,
            content: '',
            publishedAt: null,
            user: null,
            seo: null,
        ); // TODO: Use Article DTO
        $article->setCreatedAt(new \DateTimeImmutable());
        $this->fillArticle($article, $data); // TODO: Remove this piece of SHIT !!!

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
     * @throws ArticleException
     */
    public function updateCategoryId(int $oldCategoryId, int $newCategoryId): void
    {
        $articles = $this->articleRepository->findBy(['categoryId' => $oldCategoryId]);
        foreach ($articles as $article) {
            $newCategory = $this->categoryRepository->findById($newCategoryId);
            if (!$newCategory) {
                throw new ArticleException('Category not found: ' . $newCategoryId, 500);
            }
            $article->setCategory($newCategory);
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
        $articles = $this->articleRepository->findBySearch($search, $categoryId, $limit, $offset);

        if (!$articles) {
            return null;
        }

        $result = [];
        foreach ($articles as $article) {
            $result[$article->getId()] = $this->articleToArray($article);
        }

        return $result;
    }

    /**
     * @return array<string, mixed>
     */
    private function articleToArray(Article $article): array
    {
        $seo = $article->getSeo();

        return [ // TODO: Use Article DTO, REWORK NEEDED !!!
            'id' => $article->getId(),
            'name_url' => $article->getNameUrl(),
            'category_id' => $article->getCategory()?->getId(),
            'title' => $article->getTitle(),
            'content' => $article->getContent(),
            'published' => $article->getPublishedAt() !== null,
            'published_at' => $article->getPublishedAt(),
            'user_id' => $article->getUser()?->getId(),
            'robots' => $seo?->getRobots(),
            'canonical_url' => $seo?->getCanonicalUrl(),
            'og_title' => $seo?->getOgTitle(),
            'og_description' => $seo?->getOgDescription(),
            'og_image' => $seo?->getOgImage(),
            'og_url' => $seo?->getOgUrl(),
            'og_type' => $seo?->getOgType(),
            'meta_title' => $seo?->getMetaTitle(),
            'meta_description' => $seo?->getMetaDescription(),
        ];
    }

    /**
     * @param array<string, mixed> $data
     * @throws ArticleException
     */
    private function fillArticle(Article $article, array $data): void
    {
        if (isset($data['name_url'])) {
            $article->setNameUrl($data['name_url']);
        }
        if (isset($data['category_id'])) {
            $category = $this->categoryRepository->findById($data['category_id']);
            if (!$category) {
                throw new ArticleException('Category not found: ' . $data['category_id'], 500);
            }
            $article->setCategory($category);
        }
        if (isset($data['title'])) {
            $article->setTitle($data['title']);
        }
        if (isset($data['content'])) {
            $article->setContent($data['content']);
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
            $user = $this->userRepository->findById($data['user_id']);
            if ($user === null) {
                throw new ArticleException('User not found: ' . $data['user_id'], 500);
            }
            $article->setUser($user);
        }

        if ($article->getSeo() !== null) {
            // TODO: seoService->updateSeo(...);
            $seo = $article->getSeo()
                ->setRobots($data['robots'] ?? $article->getSeo()->getRobots())
                ->setCanonicalUrl($data['canonical_url'] ?? $article->getSeo()->getCanonicalUrl())
                ->setOgTitle($data['og_title'] ?? $article->getSeo()->getOgTitle())
                ->setOgDescription($data['og_description'] ?? $article->getSeo()->getOgDescription())
                ->setOgImage($data['og_image'] ?? $article->getSeo()->getOgImage())
                ->setOgUrl($data['og_url'] ?? $article->getSeo()->getOgUrl())
                ->setOgType($data['og_type'] ?? $article->getSeo()->getOgType())
                ->setMetaTitle($data['meta_title'] ?? $article->getSeo()->getMetaTitle())
                ->setMetaDescription($data['meta_description'] ?? $article->getSeo()->getMetaDescription())
                ->setMetaKeywords($data['meta_keywords'] ?? $article->getSeo()->getMetaKeywords());

            $this->seoRepository->save($seo);
        } else {
            // TODO: seoService->createSeo(...);
            $seo = $this->seoRepository->create(
                robots: $data['robots'] ?? null,
                canonicalUrl: $data['canonical_url'] ?? null,
                ogTitle: $data['og_title'] ?? null,
                ogDescription: $data['og_description'] ?? null,
                ogImage: $data['og_image'] ?? null,
                ogUrl: $data['og_url'] ?? null,
                ogType: $data['og_type'] ?? null,
                metaTitle: $data['meta_title'] ?? null,
                metaDescription: $data['meta_description'] ?? null,
                metaKeywords: $data['meta_keywords'] ?? null,
            );

            $article->setSeo($seo);
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
        return $this->articleRepository->countBySearch($search);
    }
}
