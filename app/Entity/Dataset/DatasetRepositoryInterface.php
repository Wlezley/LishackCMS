<?php

declare(strict_types=1);

namespace App\Entity\Dataset;

use App\Entity\BaseRepositoryInterface;

/**
 * @extends BaseRepositoryInterface<Dataset>
 */
interface DatasetRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * @return list<Dataset>
     */
    public function findBySearch(?string $search = null, ?int $limit = null, ?int $offset = null, bool $includeDeleted = false): array;

    public function countBySearch(?string $search = null, bool $includeDeleted = false): int;
}
