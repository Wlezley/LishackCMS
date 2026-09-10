<?php

declare(strict_types=1);

namespace App\Models\Dataset\Repository;

use App\Exception\DatasetException;
use App\Models\Dataset\Entity\DatasetColumn;
use App\Models\Dataset\Entity\DatasetRow;
use Doctrine\ORM\EntityManagerInterface;

final class DataRepository
{
    public const TABLE_NAME_PREFIX = 'dataset_data_';
    public const DATA_COLUMN_PREFIX = 'data_';

    public function __construct(
        private EntityManagerInterface $entityManager,
        private ColumnRepository $columnRepository
    ) {
    }

    private function getTableName(int $datasetId): string
    {
        return self::TABLE_NAME_PREFIX . $datasetId;
    }

    /** @return DatasetRow[] */
    public function findAll(int $datasetId): array
    {
        $columns = $this->columnRepository->findByDatasetId($datasetId);
        $connection = $this->entityManager->getConnection();
        $tableName = $this->getTableName($datasetId);
        $sql = "SELECT * FROM `$tableName`";
        $data = $connection->fetchAllAssociative($sql);

        $result = [];
        foreach ($data as $row) {
            $result[] = DatasetRow::fromDatabaseRow($row, $columns);
        }

        return $result;
    }

    public function findById(int $datasetId, int $id): ?DatasetRow
    {
        $columns = $this->columnRepository->findByDatasetId($datasetId);
        $connection = $this->entityManager->getConnection();
        $tableName = $this->getTableName($datasetId);
        $sql = "SELECT * FROM `$tableName` WHERE id = ?";
        $row = $connection->fetchAssociative($sql, [$id]);

        return $row ? DatasetRow::fromDatabaseRow($row, $columns) : null;
    }

    /** @param DatasetColumn[] $columns */
    public function createTable(int $datasetId, array $columns): void
    {
        $parts = ['`id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY'];

        /** @var DatasetColumn $column */
        foreach ($columns as $column) {
            if ($column->datasetId != $datasetId) {
                throw new DatasetException("Dataset ID for column '{$column->slug}' not match.");
            }

            $parts[] = $column->getColumnSqlDefinition();
        }

        // TODO: Refactor SQL query construction
        $sql = sprintf(
            'CREATE TABLE IF NOT EXISTS `%s` (%s) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;',
            $this->getTableName($datasetId),
            implode(', ', $parts),
        );

        $this->entityManager->getConnection()->executeStatement($sql);
    }

    /**
     * Updates an existing dataset table structure to match the given columns.
     *
     * @param int $datasetId ID of the dataset.
     * @param DatasetColumn[] $columns New columns definition.
     */
    public function updateTable(int $datasetId, array $columns): void
    {
        $tableName = $this->getTableName($datasetId);

        // TODO: Refactor SQL query construction
        $existingColumns = $this->db->fetchAll("SHOW COLUMNS FROM `$tableName`"); // @phpstan-ignore-line

        if (!$existingColumns) {
            throw new DatasetException("Dataset table '$tableName' does not exist.");
        }

        $existing = [];
        foreach ($existingColumns as $row) {
            $existing[$row['Field']] = $row;
        }

        $new = [];
        foreach ($columns as $column) {
            if ($column->datasetId != $datasetId) {
                throw new DatasetException("Dataset ID for column '{$column->slug}' does not match.");
            }

            $columnName = $column->getDatabaseColumnName();
            $new[$columnName] = $column;
        }

        $alterParts = [];

        foreach ($new as $name => $column) {
            $definition = $column->getColumnSqlDefinition();

            if (!isset($existing[$name])) {
                $alterParts[] = "ADD $definition";
            } else {
                $alterParts[] = "MODIFY $definition";
            }
        }

        // foreach ($existing as $name => $_) {
        //     if ($name === 'id') {
        //         continue; // Keep primary key
        //     }
        //     if (!isset($new[$name])) {
        //         $alterParts[] = "DROP `$name`";
        //     }
        // }

        // TODO: Refactor SQL query construction
        if (!empty($alterParts)) {
            $sql = sprintf(
                'ALTER TABLE `%s` %s;',
                $tableName,
                implode(', ', $alterParts)
            );

            $this->entityManager->getConnection()->executeStatement($sql);
        }
    }

    public function insert(int $datasetId, DatasetRow $row): DatasetRow
    {
        $connection = $this->entityManager->getConnection();
        $tableName = $this->getTableName($datasetId);
        $data = $row->toDatabaseRow();

        $connection->insert($tableName, $data);
        $row->id = (int) $connection->lastInsertId();

        return $row;
    }

    public function update(int $datasetId, DatasetRow $row): int
    {
        if ($row->id === null) {
            throw new DatasetException('Dataset Row Entity must have an ID to be updated.');
        }

        $connection = $this->entityManager->getConnection();
        $tableName = $this->getTableName($datasetId);
        $data = $row->toDatabaseRow();

        return (int) $connection->update($tableName, $data, ['id' => $row->id]);
    }

    public function delete(int $datasetId, int $rowId): int
    {
        $connection = $this->entityManager->getConnection();
        $tableName = $this->getTableName($datasetId);

        return (int) $connection->delete($tableName, ['id' => $rowId]);
    }

    /**
     * @return array<int, array<string, mixed>>|null
     */
    public function getList(int $datasetId, ?int $limit = 50, ?int $offset = 0, ?string $search = null): ?array
    {
        $connection = $this->entityManager->getConnection();
        $tableName = $this->getTableName($datasetId);
        $qb = $connection->createQueryBuilder();

        $qb->select('*')
            ->from("`$tableName`", 'd')
            ->orderBy('id', 'ASC');

        if ($limit !== null) {
            $qb->setMaxResults($limit);
        }

        if ($offset !== null) {
            $qb->setFirstResult($offset);
        }

        if ($search !== null) {
            $searchColumns = $this->columnRepository->getSearchColumns($datasetId);
            foreach ($searchColumns as $column) {
                $qb->orWhere("`$column` LIKE :search");
            }
            $qb->setParameter('search', '%' . $search . '%');
        }

        $data = $qb->executeQuery()->fetchAllAssociative();

        if (!$data) {
            return null;
        }

        $result = [];
        foreach ($data as $row) {
            $result[$row['id']] = $row;
        }

        return $result;
    }

    public function getCount(int $datasetId, ?string $search = null): int
    {
        $connection = $this->entityManager->getConnection();
        $tableName = $this->getTableName($datasetId);
        $qb = $connection->createQueryBuilder();

        $qb->select('COUNT(*)')
            ->from("`$tableName`", 'd');

        if ($search !== null) {
            $searchColumns = $this->columnRepository->getSearchColumns($datasetId);
            foreach ($searchColumns as $column) {
                $qb->orWhere("`$column` LIKE :search");
            }
            $qb->setParameter('search', '%' . $search . '%');
        }

        return (int) $qb->executeQuery()->fetchOne();
    }
}
