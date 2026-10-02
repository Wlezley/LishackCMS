<?php

declare(strict_types=1);

namespace App\Entity\Dataset;

use App\Entity\BaseRepositoryInterface;

/**
 * @extends BaseRepositoryInterface<Dataset>
 */
interface DatasetRepositoryInterface extends BaseRepositoryInterface
{
    public function findBySlug(string $slug): ?Dataset;

    public function create(
        string $name,
        string $slug,
        string $component,
        string $presenter,
        bool $active = true,
        bool $deleted = false,
    ): Dataset;

    public function deleteById(int $id): bool;

    /**
     * @return \App\Entity\Dataset\Dataset[]
     */
    public function findBySearch(
        ?int $limit = null,
        ?int $offset = null,
        bool $includeDeleted = false,
        ?string $search = null,
    ): array;

    public function countBySearch(?string $search = null, bool $includeDeleted = false): int;
}
