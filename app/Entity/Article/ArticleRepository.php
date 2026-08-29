<?php

declare(strict_types=1);

namespace App\Entity\Article;

use App\Entity\BaseRepository;

/**
 * @extends BaseRepository<Article>
 */
final readonly class ArticleRepository extends BaseRepository implements ArticleRepositoryInterface
{
}
