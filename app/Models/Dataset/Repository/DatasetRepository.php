<?php

declare(strict_types=1);

namespace App\Models\Dataset\Repository;

use App\Entity\Dataset\Dataset as DatasetEntity;
use App\Entity\Dataset\DatasetRepository as DoctrineRepository;
use App\Exception\DatasetException;
use App\Models\Dataset\Entity\Dataset;

final class DatasetRepository
{
    public const TABLE_NAME = 'dataset';

    public function __construct(
        private DoctrineRepository $doctrineRepository,
    ) {
    }

    public function findById(int $id): ?Dataset
    {
        $entity = $this->doctrineRepository->findById($id);
        return $entity ? $this->entityToModel($entity) : null;
    }

    public function findBySlug(string $slug): ?Dataset
    {
        $entity = $this->doctrineRepository->findOneBy(['slug' => $slug]);
        return $entity ? $this->entityToModel($entity) : null;
    }

    /** @return Dataset[] */
    public function findAll(): array
    {
        $entities = $this->doctrineRepository->findBy([], ['id' => 'ASC']);
        $result = [];
        foreach ($entities as $entity) {
            $result[] = $this->entityToModel($entity);
        }
        return $result;
    }

    public function insert(Dataset $dataset): Dataset
    {
        $entity = new DatasetEntity(
            (string)$dataset->name,
            (string)$dataset->slug,
            (string)$dataset->component,
            (string)$dataset->presenter,
            true, // active
            (bool)$dataset->deleted
        );

        $this->doctrineRepository->save($entity);
        $dataset->id = $entity->getId();
        return $dataset;
    }

    public function update(Dataset $dataset): void
    {
        if ($dataset->id === null) {
            throw new DatasetException('Cannot update dataset without ID.');
        }

        $entity = $this->doctrineRepository->findById($dataset->id);
        if (!$entity) {
            throw new DatasetException("Dataset ID '$dataset->id' not found.");
        }

        $entity->setName((string)$dataset->name);
        $entity->setSlug((string)$dataset->slug);
        $entity->setComponent((string)$dataset->component);
        $entity->setPresenter((string)$dataset->presenter);
        $entity->setDeleted((bool)$dataset->deleted);

        $this->doctrineRepository->save($entity);
    }

    public function delete(int $id): int
    {
        $entity = $this->doctrineRepository->findById($id);
        if ($entity) {
            $this->doctrineRepository->delete($entity);
            return 1;
        }
        return 0;
    }

    public function setDeleted(int $id, bool $deleted = true): int
    {
        $entity = $this->doctrineRepository->findById($id);
        if ($entity) {
            $entity->setDeleted($deleted);
            $this->doctrineRepository->save($entity);
            return 1;
        }
        return 0;
    }

    private function entityToModel(DatasetEntity $entity): Dataset
    {
        $model = new Dataset();
        $model->id = $entity->getId();
        $model->name = $entity->getName();
        $model->slug = $entity->getSlug();
        $model->component = $entity->getComponent();
        $model->presenter = $entity->getPresenter();
        $model->deleted = $entity->isDeleted();
        return $model;
    }

    public function exists(int $id, bool $includeDeleted = false): bool
    {
        $criteria = ['id' => $id];
        if (!$includeDeleted) {
            $criteria['deleted'] = false;
        }

        return $this->doctrineRepository->exists($criteria);
    }

    /**
     * Retrieves a list of datasets for use in the sidebar.
     *
     * Returns all datasets ordered by ID. By default, deleted datasets are excluded,
     * but this can be overridden via the `$includeDeleted` flag.
     *
     * @param bool $includeDeleted Whether to include datasets marked as deleted (default: false).
     * @return array<int|string,array<string,string|int|null>>|null Array of datasets indexed by ID, or null if none found.
     */
    public function getSidebarList(bool $includeDeleted = false): ?array
    {
        $entities = $this->doctrineRepository->findBySearch(null, null, null, $includeDeleted);

        if (!$entities) {
            return null;
        }

        $result = [];
        foreach ($entities as $entity) {
            $result[$entity->getId()] = [
                'id' => $entity->getId(),
                'name' => $entity->getName(),
                'slug' => $entity->getSlug(),
            ];
        }

        return $result;
    }

    /**
     * Retrieves a list of datasets with optional search and pagination.
     *
     * @param int<0, max>|null $limit Number of results to return (default: 50).
     * @param int<0, max>|null $offset Offset for pagination (default: 0).
     * @param string|null $search Optional search query for name, slug, component, or presenter fields.
     * @return array<int,array<string,int|string|bool>>|null Array of datasets indexed by ID, or null if none found.
     */
    public function getList(?int $limit = 50, ?int $offset = 0, ?string $search = null): ?array
    {
        $entities = $this->doctrineRepository->findBySearch($search, $limit, $offset, false);

        if (!$entities) {
            return null;
        }

        $result = [];
        foreach ($entities as $entity) {
            $result[$entity->getId()] = [
                'id' => $entity->getId(),
                'name' => $entity->getName(),
                'slug' => $entity->getSlug(),
                'component' => $entity->getComponent(),
                'presenter' => $entity->getPresenter(),
                'deleted' => $entity->isDeleted(),
            ];
        }

        return $result;
    }

    public function getCount(?string $search = null): int
    {
        return $this->doctrineRepository->countBySearch($search, false);
    }
}
