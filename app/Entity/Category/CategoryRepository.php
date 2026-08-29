<?php

declare(strict_types=1);

namespace App\Entity\Category;

use App\Entity\BaseRepository;

/**
 * @extends BaseRepository<Category>
 */
final readonly class CategoryRepository extends BaseRepository implements CategoryRepositoryInterface
{
}
