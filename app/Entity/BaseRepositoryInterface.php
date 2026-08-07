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
}
