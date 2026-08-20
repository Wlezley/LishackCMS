<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

/**
 * @template TEntity of object
 *
 * @implements BaseRepositoryInterface<TEntity>
 */
abstract readonly class BaseRepository implements BaseRepositoryInterface
{
    /**
     * @var EntityRepository<TEntity>
     */
    private EntityRepository $repository;

    public function __construct(
        protected EntityManagerInterface $entityManager,
    ) {
        /** @var EntityRepository<TEntity> $repository */
        $repository = $entityManager->getRepository(
            RepositoryEntityMap::resolveEntityClass(static::class),
        );

        $this->repository = $repository;
    }

    /** @inheritDoc */
    public function findById(int $id): ?object
    {
        return $this->repository->find($id);
    }

    /** @inheritDoc */
    public function findAll(): array
    {
        /** @var list<TEntity> $entities */
        $entities = $this->repository->findAll();

        return $entities;
    }

    /**
     * Finds a single entity by criteria.
     *
     * @param array<string, mixed> $criteria
     * @return TEntity|null
     */
    protected function findOneBy(array $criteria): ?object
    {
        return $this->repository->findOneBy($criteria);
    }

    /**
     * Finds entities by criteria.
     *
     * @param array<string, mixed> $criteria
     * @param array<string, 'ASC'|'DESC'>|null $orderBy
     * @return list<TEntity>
     */
    protected function findBy(
        array $criteria,
        ?array $orderBy = null,
        ?int $limit = null,
        ?int $offset = null,
    ): array {
        /** @var list<TEntity> $entities */
        $entities = $this->repository->findBy($criteria, $orderBy, $limit, $offset);

        return $entities;
    }

    /**
     * Counts entities matching the given criteria.
     *
     * @param array<string, mixed> $criteria
     * @return int<0, max>
     */
    protected function count(array $criteria = []): int
    {
        return $this->repository->count($criteria);
    }

    /**
     * Checks whether an entity matching the given criteria exists.
     *
     * @param array<string, mixed> $criteria
     */
    public function exists(array $criteria): bool
    {
        return $this->findOneBy($criteria) !== null;
    }

    /** @inheritDoc */
    public function add(object $entity): void
    {
        $this->entityManager->persist($entity);
    }

    /** @inheritDoc */
    public function save(object $entity): void
    {
        $this->add($entity);
        $this->flush();
    }

    /** @inheritDoc */
    public function remove(object $entity): void
    {
        $this->entityManager->remove($entity);
    }

    /** @inheritDoc */
    public function delete(object $entity): void
    {
        $this->remove($entity);
        $this->flush();
    }

    /** @inheritDoc */
    public function flush(): void
    {
        $this->entityManager->flush();
    }
}
