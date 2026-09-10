<?php

declare(strict_types=1);

namespace App\Entity\CmsConfig;

use App\Entity\BaseRepositoryInterface;

/**
 * @extends BaseRepositoryInterface<CmsConfig>
 */
interface CmsConfigRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * @return list<CmsConfig>
     */
    public function findBySearch(?string $category = null, ?string $search = null, ?int $limit = null, ?int $offset = null): array;

    public function countBySearch(?string $category = null, ?string $search = null): int;
}
