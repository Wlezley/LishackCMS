<?php

declare(strict_types=1);

namespace App\Models\Dataset\Repository;

use App\Entity\DatasetColumn\DatasetColumn as DatasetColumnEntity;
use App\Entity\DatasetColumn\DatasetColumnRepository as DoctrineRepository;
use App\Exception\DatasetException;
use App\Models\Dataset\Entity\DatasetColumn;
use App\Models\Helpers\SqlHelper;
use Doctrine\ORM\EntityManagerInterface;

final class ColumnRepository
{
    public const TABLE_NAME = 'dataset_column';

    public function __construct(
        private DoctrineRepository $doctrineRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    /** @return DatasetColumn[] */
    public function findByDatasetId(int $datasetId, bool $includeDeleted = false): array
    {
        $criteria = ['datasetId' => $datasetId];
        if (!$includeDeleted) {
            $criteria['deleted'] = false;
        }

        $entities = $this->doctrineRepository->findBy($criteria, ['columnId' => 'ASC']);

        $result = [];
        foreach ($entities as $entity) {
            $column = $this->entityToModel($entity);
            $result[$column->columnId] = $column;
        }

        return $result;
    }

    public function findColumn(int $datasetId, int $columnId, bool $includeDeleted = false): ?DatasetColumn
    {
        $criteria = [
            'datasetId' => $datasetId,
            'columnId' => $columnId,
        ];

        if (!$includeDeleted) {
            $criteria['deleted'] = false;
        }

        $entity = $this->doctrineRepository->findOneBy($criteria);

        return $entity ? $this->entityToModel($entity) : null;
    }

    public function lastColumnId(int $datasetId): int
    {
        return $this->doctrineRepository->getMaxColumnId($datasetId);
    }

    public function insert(DatasetColumn $column): DatasetColumn
    {
        if ($column->datasetId === 0) {
            throw new DatasetException('Cannot insert column without dataset ID.');
        }

        $column->columnId = $this->lastColumnId($column->datasetId) + 1;

        $entity = new DatasetColumnEntity(
            $column->datasetId,
            $column->columnId,
            (string)$column->name,
            (string)$column->slug,
            (string)$column->type,
            (bool)$column->required,
            (bool)$column->listed,
            (bool)$column->hidden,
            (bool)$column->deleted,
            $column->default
        );

        $this->doctrineRepository->save($entity);
        return $this->entityToModel($entity);
    }

    public function update(DatasetColumn $column): int
    {
        if ($column->datasetId === 0) {
            throw new DatasetException('Cannot update column without dataset ID.');
        }
        if ($column->columnId === null) {
            throw new DatasetException('Cannot update column without column ID.');
        }

        $entity = $this->doctrineRepository->findOneBy([
            'datasetId' => $column->datasetId,
            'columnId' => $column->columnId,
        ]);

        if (!$entity) {
            return 0;
        }

        $entity->setName((string)$column->name);
        $entity->setSlug((string)$column->slug);
        $entity->setType((string)$column->type);
        $entity->setRequired((bool)$column->required);
        $entity->setListed((bool)$column->listed);
        $entity->setHidden((bool)$column->hidden);
        $entity->setDeleted((bool)$column->deleted);
        $entity->setDefault($column->default);

        $this->doctrineRepository->save($entity);
        return 1;
    }

    private function entityToModel(DatasetColumnEntity $entity): DatasetColumn
    {
        $model = new DatasetColumn();
        $model->datasetId = $entity->getDatasetId();
        $model->columnId = $entity->getColumnId();
        $model->name = $entity->getName();
        $model->slug = $entity->getSlug();
        $model->type = $entity->getType();
        $model->required = $entity->isRequired();
        $model->listed = $entity->isListed();
        $model->hidden = $entity->isHidden();
        $model->deleted = $entity->isDeleted();
        $model->default = $entity->getDefault();
        return $model;
    }

    /**
     * @param int $datasetId ID of Dataset
     * @param int $columnId ID of Column in the Dataset
     *
     * @return int Affected rows
     * @throws DatasetException If column does not exist
     */
    public function delete(int $datasetId, int $columnId): int
    {
        $columnName = DataRepository::DATA_COLUMN_PREFIX . $columnId;

        if (!$this->columnExists($datasetId, $columnName)) {
            throw new DatasetException("Column '{$columnName}' does not exist.");
        }

        $entity = $this->doctrineRepository->findOneBy([
            'datasetId' => $datasetId,
            'columnId' => $columnId,
        ]);

        if ($entity) {
            $entity->setDeleted(true);
            $this->doctrineRepository->save($entity);
            return 1;
        }
        return 0;
    }

    /**
     * @param int $datasetId ID of Dataset
     * @return int Affected rows
     */
    public function deleteAllColumns(int $datasetId): int
    {
        $entities = $this->doctrineRepository->findBy(['datasetId' => $datasetId]);
        foreach ($entities as $entity) {
            $entity->setDeleted(true);
            $this->doctrineRepository->add($entity);
        }
        $this->doctrineRepository->flush();
        return count($entities);
    }

    /**
     * @param int $datasetId ID of Dataset
     * @return int Count of columns
     */
    public function columnCount(int $datasetId, bool $includeDeleted = false): int
    {
        $criteria = ['datasetId' => $datasetId];
        if (!$includeDeleted) {
            $criteria['deleted'] = false;
        }

        return $this->doctrineRepository->count($criteria);
    }

    private function columnExists(int $datasetId, string $columnName): bool
    {
        $tableName = DataRepository::TABLE_NAME_PREFIX . $datasetId;

        SqlHelper::assertSafeIdentifier($tableName);
        SqlHelper::assertSafeIdentifier($columnName);

        $connection = $this->entityManager->getConnection();
        $sql = "SHOW COLUMNS FROM `$tableName` WHERE FIELD = ?";
        $result = $connection->fetchAssociative($sql, [$columnName]);

        return $result !== false;
    }

    /** @return array<string> */
    public function getSearchColumns(int $datasetId): array
    {
        $entities = $this->doctrineRepository->findBy([
            'datasetId' => $datasetId,
            'listed' => true,
            'deleted' => false,
        ]);

        $columns = [];
        foreach ($entities as $entity) {
            $columns[] = "data_{$entity->getColumnId()}";
        }

        return $columns;
    }
}
