<?php

declare(strict_types=1);

namespace App\Models\Dataset\Repository;

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
    public function findByDatasetId(int $datasetId): array
    {
        $result = [];
        $rows = $this->db->table(self::TABLE_NAME)
            ->where('dataset_id', $datasetId)
            ->order('column_id');

        foreach ($rows as $row) {
            $result[] = DatasetColumn::fromDatabaseRow($row->toArray());
        }

        return $result;
    }

    public function findColumn(int $datasetId, int $columnId): ?DatasetColumn
    {
        $row = $this->db->table(self::TABLE_NAME)
            ->where([
                'dataset_id' => $datasetId,
                'column_id' => $columnId,
            ])->fetch();

        return $row ? DatasetColumn::fromDatabaseRow($row->toArray()) : null;
    }

    public function findLastColumnId(int $datasetId): int
    {
        $max = $this->db->table(self::TABLE_NAME)
            ->where('dataset_id', $datasetId)
            ->max('column_id');

        return $max ? (int) $max : 0;
    }

    public function insert(DatasetColumn $column): DatasetColumn
    {
        $column->columnId = $this->findLastColumnId($column->datasetId);
        $column->columnId++;

        $row = $this->db->table(self::TABLE_NAME)->insert($column->toDatabaseRow());
        return DatasetColumn::fromDatabaseRow($row->toArray());
    }

    public function update(DatasetColumn $column): int
    {
        if ($column->datasetId === 0) {
            throw new \InvalidArgumentException('Cannot update column without dataset ID.');
        }
        if ($column->columnId === null) {
            throw new \InvalidArgumentException('Cannot update column without column ID.');
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
     * @return int Affected rows
     * @throws \InvalidArgumentException If column does not exist
     */
    public function delete(int $datasetId, int $columnId): int
    {
        $columnName = DataRepository::DATA_COLUMN_PREFIX . $columnId;

        if (!$this->columnExists($datasetId, $columnName)) {
            throw new \InvalidArgumentException("Column '{$columnName}' does not exist.");
        }

        return $this->db->table(self::TABLE_NAME)
            ->where([
                'dataset_id' => $datasetId,
                'column_id' => $columnId
            ])->update([
                'deleted' => 1
            ]);
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
}
