<?php

declare(strict_types=1);

namespace App\Models\Dataset\Repository;

use App\Models\Dataset\Entity\DatasetColumn;
use App\Models\Dataset\Entity\DatasetRow;
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
                throw new \InvalidArgumentException("Dataset ID for column '{$column->slug}' not match.");
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
            throw new \InvalidArgumentException('Dataset Row Entity must have an ID to be updated.');
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
}
