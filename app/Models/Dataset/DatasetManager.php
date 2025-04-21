<?php

declare(strict_types=1);

namespace App\Models\Dataset;

use App\Models\Dataset\Entity\Dataset;
use App\Models\Dataset\Entity\DatasetColumn;
use App\Models\Dataset\Entity\DatasetRow;
use App\Models\Dataset\Repository\ColumnRepository;
use App\Models\Dataset\Repository\DataRepository;
use App\Models\Dataset\Repository\DatasetRepository;

final class DatasetManager
{
    private Dataset $dataset;

    /** @var DatasetColumn[] $columns */
    private array $columns;

    public function __construct(
        private DatasetRepository $datasetRepository,
        private ColumnRepository $columnRepository,
        private DataRepository $dataRepository
    ) {}

    /** Načtení datasetu vč. nastavení jeho sloupců, se kterým budeme pracovat, podle jeho ID */
    public function loadDatasetById(int $id): void
    {
        $this->dataset = $this->datasetRepository->findById($id);
        $this->columns = $this->columnRepository->findByDatasetId($this->dataset->id);
    }

    /** @return DatasetColumn[] */
    public function getColumnSchema(): array
    {
        return $this->columns;
    }

    // TODO: JUST IDEAS... To be used or delete...

    // /** @return DatasetRow[] */
    // public function getRows(int $datasetId): array
    // {
    //     return [];
    // }

    // public function getRow(int $datasetId, int $id): ?DatasetRow
    // {
    //     $row = $this->dataRepository->findById($datasetId, $id);

    //     if (!$row) {
    //         return null;
    //     }

    //     return $row;
    // }

    // public function insertRow(int $datasetId, DatasetRow $row): void
    // {
    //     $this->dataRepository->insert($datasetId, $row);
    // }

    // public function updateRow(int $datasetId, DatasetRow $row): void
    // {
    //     if ($row->id === null) {
    //         throw new \InvalidArgumentException('Row must have an ID to update.');
    //     }

    //     $this->dataRepository->update($datasetId, $row);
    // }

    public function deleteRow(int $datasetId, int $rowId): void
    {
        $this->dataRepository->delete($datasetId, $rowId);
    }

    // public function getDatasetWithColumns(int $datasetId): Dataset
    // {
    //     $dataset = $this->datasetRepository->findById($datasetId);
    //     return $dataset;
    // }
}
