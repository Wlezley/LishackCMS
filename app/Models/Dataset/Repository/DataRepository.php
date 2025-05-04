<?php

declare(strict_types=1);

namespace App\Models\Dataset\Repository;

use App\Models\Dataset\DatasetException;
use App\Models\Dataset\Entity\DatasetColumn;
use App\Models\Dataset\Entity\DatasetRow;
use App\Models\Helpers\ArrayHelper;
use Nette\Database\Explorer;

final class DataRepository
{
    public const TABLE_NAME_PREFIX = 'dataset_data_';
    public const DATA_COLUMN_PREFIX = 'data_';

    public function __construct(
        private Explorer $db,
        private ColumnRepository $columnRepository
    ) {}

    private function getTableName(int $datasetId): string
    {
        return self::TABLE_NAME_PREFIX . $datasetId;
    }

    /** @return DatasetRow[] */
    public function findAll(int $datasetId): array
    {
        $columns = $this->columnRepository->findByDatasetId($datasetId);

        $result = [];
        foreach ($this->db->table($this->getTableName($datasetId)) as $row) {
            $result[] = DatasetRow::fromDatabaseRow($row->toArray(), $columns);
        }

        return $result;
    }

    public function findById(int $datasetId, int $id): ?DatasetRow
    {
        $columns = $this->columnRepository->findByDatasetId($datasetId);

        $row = $this->db->table($this->getTableName($datasetId))
            ->where('id', $id)
            ->fetch();

        return $row ? DatasetRow::fromDatabaseRow($row->toArray(), $columns) : null;
    }

    /** @param DatasetColumn[] $columns */
    public function createTable(int $datasetId, array $columns): void
    {
        $parts = ["`id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY"];

        /** @var DatasetColumn $column */
        foreach ($columns as $column) {
            if ($column->datasetId != $datasetId) {
                throw new DatasetException("Dataset ID for column '{$column->slug}' not match.");
            }

            $parts[] = $column->getColumnSqlDefinition();
        }

        $sql = sprintf(
            "CREATE TABLE IF NOT EXISTS `%s` (%s) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
            $this->getTableName($datasetId),
            implode(", ", $parts)
        );

        $this->db->query($sql);
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
        $existingColumns = $this->db->fetchAll("SHOW COLUMNS FROM `$tableName`");

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

        if (!empty($alterParts)) {
            $sql = sprintf(
                "ALTER TABLE `%s` %s;",
                $tableName,
                implode(", ", $alterParts)
            );

            $this->db->query($sql);
        }
    }

    public function insert(int $datasetId, DatasetRow $row): DatasetRow
    {
        $dbRow = $this->db->table($this->getTableName($datasetId))
            ->insert($row->toDatabaseRow());

        $row->id = (int) $dbRow->getPrimary();

        return $row;
    }

    public function update(int $datasetId, DatasetRow $row): int
    {
        if ($row->id === null) {
            throw new DatasetException('Dataset Row Entity must have an ID to be updated.');
        }

        return $this->db->table($this->getTableName($datasetId))
            ->where('id', $row->id)
            ->update($row->toDatabaseRow());
    }

    public function delete(int $datasetId, int $rowId): int
    {
        return $this->db->table($this->getTableName($datasetId))
            ->where('id', $rowId)
            ->delete();
    }

    /**
     * Retrieves a list of datasets with optional search and pagination.
     *
     * @param int $limit Number of results to return (default: 50).
     * @param int $offset Offset for pagination (default: 0).
     * @param string|null $search Optional search query for name, slug, component, or presenter fields.
     * @return array<int|string,array<string,string|int|null>>|null Array of datasets indexed by ID, or null if none found.
     */
    public function getList(int $datasetId, int $limit = 50, int $offset = 0, ?string $search = null): ?array
    {
        $query = $this->db->table($this->getTableName($datasetId))
            ->limit($limit, $offset)
            ->order('id ASC');

        $searchColumns = $this->columnRepository->getSearchColumns($datasetId);

        $whereParts = [];
        foreach ($searchColumns as $column) {
            $whereParts["$column LIKE ?"] = "%$search%";
        }
        $query->whereOr($whereParts);

        $data = $query->fetchAll();

        return $data ? ArrayHelper::resultToArray($data) : null;
    }

    public function getCount(int $datasetId, ?string $search = null): int
    {
        $query = $this->db->table($this->getTableName($datasetId));

        $searchColumns = $this->columnRepository->getSearchColumns($datasetId);

        $whereParts = [];
        foreach ($searchColumns as $column) {
            $whereParts["$column LIKE ?"] = "%$search%";
        }
        $query->whereOr($whereParts);

        return $query->count('*');
    }
}
