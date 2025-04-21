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
        $tableName = $this->getTableName($datasetId);
        $result = [];

        foreach ($this->db->table($tableName) as $row) {
            $result[] = DatasetRow::fromDatabaseRow($row->toArray(), $columns);
        }

        return $result;
    }

    public function findById(int $datasetId, int $id): ?DatasetRow
    {
        $columns = $this->columnRepository->findByDatasetId($datasetId);
        $tableName = $this->getTableName($datasetId);

        $row = $this->db->table($tableName)
            ->where('id', $id)
            ->fetch();

        return $row ? DatasetRow::fromDatabaseRow($row->toArray(), $columns) : null;
    }

    /** @param DatasetColumn[] $columns */
    public function createTable(int $datasetId, array $columns): void
    {
        $tableName = DataRepository::TABLE_NAME_PREFIX . $datasetId;
        // SqlHelper::assertSafeIdentifier($tableName); // N/A

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
            $tableName,
            implode(", ", $parts)
        );

        $this->db->query($sql);
    }

    public function insert(int $datasetId, DatasetRow $row): DatasetRow
    {
        $tableName = $this->getTableName($datasetId);
        $insertData = $row->toDatabaseRow();

        $dbRow = $this->db->table($tableName)->insert($insertData);
        $row->id = (int) $dbRow->getPrimary();

        return $row;
    }

    public function update(int $datasetId, DatasetRow $row): void
    {
        if ($row->id === null) {
            throw new \InvalidArgumentException('Dataset Row Entity must have an ID to be updated.');
        }

        $tableName = $this->getTableName($datasetId);
        $this->db->table($tableName)
            ->where('id', $row->id)
            ->update($row->toDatabaseRow());
    }

    public function delete(int $datasetId, int $rowId): void
    {
        $tableName = $this->getTableName($datasetId);
        $this->db->table($tableName)
            ->where('id', $rowId)
            ->delete();
    }
}
