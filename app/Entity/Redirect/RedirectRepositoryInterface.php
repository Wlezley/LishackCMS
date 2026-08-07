<?php

declare(strict_types=1);

namespace App\Entity\Redirect;

use App\Entity\BaseRepositoryInterface;

/**
 * @extends BaseRepositoryInterface<Redirect>
 */
interface RedirectRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Finds a redirect by its source URL.
     */
    public function findBySource(string $source): ?Redirect;

    /**
     * Finds an enabled redirect by its source URL.
     */
    public function findEnabledBySource(string $source): ?Redirect;

    /**
     * Returns all enabled redirects.
     *
     * @return list<Redirect>
     */
    public function findAllEnabled(): array;

    /**
     * Checks whether a redirect with the given source URL exists.
     */
    public function existsBySource(string $source): bool;

    /**
     * Returns the number of redirects matching the search criteria.
     */
    public function countBySearch(?string $search = null): int;

    /**
     * Returns a paginated list of redirects.
     *
     * @return list<Redirect>
     */
    public function findPaginated(
        int $limit,
        int $offset,
        ?string $search = null,
    ): array;
}
