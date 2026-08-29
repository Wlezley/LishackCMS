<?php

declare(strict_types=1);

namespace App\Models\Dataset\Repository;

use App\Entity\DatasetColumn\DatasetColumn as DatasetColumnEntity;
use App\Entity\DatasetColumn\DatasetColumnRepository as DoctrineRepository;
use App\Exception\DatasetException;
use App\Models\Dataset\Entity\DatasetColumn;
use App\Models\Helpers\SqlHelper;
use Nette\Database\Explorer;

final class ColumnRepository
{
    public const TABLE_NAME = 'dataset_column';

    public function __construct(
        private Explorer $db,
        private DoctrineRepository $doctrineRepository,
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
        // For MAX(column_id) we can still use DB explorer or custom DQL
        $max = $this->db->table(self::TABLE_NAME)
            ->where('dataset_id', $datasetId)
            ->max('column_id');

        return $max ? (int) $max : 0;
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
        $query = $this->db->table(self::TABLE_NAME)
            ->where('dataset_id', $datasetId);

        if (!$includeDeleted) {
            $query->where('deleted', 0);
        }

        return $query->count('*');
    }

    private function columnExists(int $datasetId, string $columnName): bool
    {
        $tableName = DataRepository::TABLE_NAME_PREFIX . $datasetId;

        SqlHelper::assertSafeIdentifier($tableName);
        SqlHelper::assertSafeIdentifier($columnName);

        // TODO: Refactor SQL query construction
        $sql = "SHOW COLUMNS FROM `$tableName` WHERE FIELD = ?";
        $result = $this->db->fetch($sql, $columnName); // @phpstan-ignore-line

        return $result !== null;
    }

    /** @return array<string> */
    public function getSearchColumns(int $datasetId): array
    {
        $query = $this->db->table(self::TABLE_NAME)
            ->select('column_id')
            ->where([
                'dataset_id' => $datasetId,
                'listed' => 1,
            ])->fetchAll();

        $columns = [];
        foreach ($query as $row) {
            $columns[] = "data_{$row['column_id']}";
        }

        return $columns;
    }
}
