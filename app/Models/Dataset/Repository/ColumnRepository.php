<?php

declare(strict_types=1);

namespace App\Models\Dataset\Repository;

use App\Models\Dataset\DatasetException;
use App\Models\Dataset\Entity\DatasetColumn;
use App\Models\Helpers\SqlHelper;
use Nette\Database\Explorer;

final class ColumnRepository
{
    public const TABLE_NAME = 'dataset_column';

    public function __construct(
        private Explorer $db
    ) {}

    /** @return DatasetColumn[] */
    public function findByDatasetId(int $datasetId, bool $includeDeleted = false): array
    {
        $query = $this->db->table(self::TABLE_NAME)
            ->where('dataset_id', $datasetId);

        if (!$includeDeleted) {
            $query->where('deleted', 0);
        }

        $rows = $query->order('column_id')->fetchAll();

        $result = [];
        foreach ($rows as $row) {
            $column = DatasetColumn::fromDatabaseRow($row->toArray());
            $result[$column->columnId] = $column;
        }

        return $result;
    }

    public function findColumn(int $datasetId, int $columnId, bool $includeDeleted = false): ?DatasetColumn
    {
        $where = [
            'dataset_id' => $datasetId,
            'column_id' => $columnId,
        ];

        if (!$includeDeleted) {
            $where['deleted'] = 0;
        }

        $row = $this->db->table(self::TABLE_NAME)
            ->where($where)
            ->fetch();

        return $row ? DatasetColumn::fromDatabaseRow($row->toArray()) : null;
    }

    public function lastColumnId(int $datasetId): int
    {
        $max = $this->db->table(self::TABLE_NAME)
            ->where('dataset_id', $datasetId)
            ->max('column_id');

        if ($max !== null && !is_int($max)) {
            $type = gettype($max);
            throw new DatasetException("Whoa... I asked for an integer, not a {$type}. Are we still in the Matrix?");
        }

        return $max ? (int) $max : 0;
    }

    public function insert(DatasetColumn $column): DatasetColumn
    {
        if ($column->datasetId === 0) {
            throw new DatasetException('Cannot insert column without dataset ID.');
        }

        $column->columnId = $this->lastColumnId($column->datasetId);
        $column->columnId++;

        $row = $this->db->table(self::TABLE_NAME)->insert($column->toDatabaseRow());
        return DatasetColumn::fromDatabaseRow($row->toArray());
    }

    public function update(DatasetColumn $column): int
    {
        if ($column->datasetId === 0) {
            throw new DatasetException('Cannot update column without dataset ID.');
        }
        if ($column->columnId === null) {
            throw new DatasetException('Cannot update column without column ID.');
        }

        return $this->db->table(self::TABLE_NAME)
            ->where([
                'dataset_id' => $column->datasetId,
                'column_id' => $column->columnId,
            ])->update($column->toDatabaseRow());
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

        return $this->db->table(self::TABLE_NAME)
            ->where([
                'dataset_id' => $datasetId,
                'column_id' => $columnId
            ])->update([
                'deleted' => 1
            ]);
    }

    /**
     * @param int $datasetId ID of Dataset
     * @return int Affected rows
     */
    public function deleteAllColumns(int $datasetId): int
    {
        return $this->db->table(self::TABLE_NAME)
            ->where('dataset_id', $datasetId)
            ->update(['deleted' => 1]);
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

        $sql = "SHOW COLUMNS FROM `$tableName` WHERE FIELD = ?";
        $result = $this->db->fetch($sql, $columnName);

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
