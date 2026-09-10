<?php

declare(strict_types=1);

namespace App\Entity\Article;

use App\Entity\BaseRepositoryInterface;

/**
 * @extends BaseRepositoryInterface<Article>
 */
interface ArticleRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * @return list<Article>
     */
    public function findBySearch(?string $search = null, ?int $categoryId = null, ?int $limit = null, ?int $offset = null): array;

    public function countBySearch(?string $search = null, ?int $categoryId = null): int;
}
