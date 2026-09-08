<?php

declare(strict_types=1);

namespace App\Entity;

/**
 * @template TEntity of object
 */
interface BaseRepositoryInterface
{
    /**
     * Finds an entity by its identifier.
     *
     * @return TEntity|null
     */
    public function findById(int $id): ?object;

    /**
     * Returns all entities.
     *
     * @return list<TEntity>
     */
    public function findAll(): array;

    /**
     * Finds a single entity by criteria.
     *
     * @param array<string, mixed> $criteria
     * @return TEntity|null
     */
    public function findOneBy(array $criteria): ?object;

    /**
     * Finds entities by criteria.
     *
     * @param array<string, mixed> $criteria
     * @param array<string, 'ASC'|'DESC'>|null $orderBy
     * @return list<TEntity>
     */
    public function findBy(
        array $criteria,
        ?array $orderBy = null,
        ?int $limit = null,
        ?int $offset = null,
    ): array;

    /**
     * Counts entities matching the given criteria.
     *
     * @param array<string, mixed> $criteria
     * @return int<0, max>
     */
    public function count(array $criteria = []): int;

    /**
     * Checks whether an entity matching the given criteria exists.
     *
     * @param array<string, mixed> $criteria
     */
    public function exists(array $criteria): bool;

    /**
     * Adds an entity to the current unit of work.
     *
     * The changes are not immediately written to the database.
     *
     * @param TEntity $entity
     */
    public function add(object $entity): void;

    /**
     * Adds an entity and immediately writes all pending changes to the database.
     *
     * @param TEntity $entity
     */
    public function save(object $entity): void;

    /**
     * Removes an entity from the current unit of work.
     *
     * The changes are not immediately written to the database.
     *
     * @param TEntity $entity
     */
    public function remove(object $entity): void;

    /**
     * Removes an entity and immediately writes all pending changes to the database.
     *
     * @param TEntity $entity
     */
    public function delete(object $entity): void;

    /**
     * Writes all pending changes to the database.
     */
    public function flush(): void;

    /**
     * @return list<string>
     */
    public function getSortableFields(): array;
}
