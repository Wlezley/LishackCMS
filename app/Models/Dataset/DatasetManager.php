<?php

declare(strict_types=1);

namespace App\Models\Dataset;

use App\Models\Dataset\Entity\Dataset;
use App\Models\Dataset\Entity\DatasetColumn;
use App\Models\Dataset\Repository\ColumnRepository;
use App\Models\Dataset\Repository\DataRepository;
use App\Models\Dataset\Repository\DatasetRepository;

final class DatasetManager
{
    private Dataset $dataset;

    /** @var DatasetColumn[] $columns */
    private array $columns = [];

    public function __construct(
        private DatasetRepository $datasetRepository,
        private ColumnRepository $columnRepository,
        private DataRepository $dataRepository
    ) {}

    public function loadDatasetById(int $id, bool $includeDeleted = false): bool
    {
        if (!$this->datasetRepository->exists($id, $includeDeleted)) {
            return false;
        }

        $this->dataset = $this->datasetRepository->findById($id);
        $this->columns = $this->columnRepository->findByDatasetId($this->dataset->id, $includeDeleted);

        return true;
    }

    public function isReady(): bool
    {
        if (!isset($this->dataset) || $this->dataset->id === null || empty($this->columns)) {
            return false;
        }

        return true;
    }

    /** @return DatasetColumn[] */
    public function getColumnsSchema(): array
    {
        return $this->columns;
    }

    /** @return array<int,mixed> */
    public function getColumnsList(): array
    {
        if (empty($this->columns)) {
            return [];
        }

        $columnList = [];

        /** @var DatasetColumn $column */
        foreach ($this->columns as $column) {
            $columnList[$column->columnId] = [
                'columnId' => $column->columnId,
                'name' => $column->name,
                'slug' => $column->slug,
                'type' => $column->type,
                'required' => $column->required,
                'deleted' => $column->deleted,
            ];
        }

        return $columnList;
    }

    public function getLastColumnId(): int
    {
        if (empty($this->columns)) {
            return 0;
        }

        return (int) max(array_keys($this->getColumnsList()));
    }

    public function deleteRow(int $datasetId, int $rowId): void
    {
        $this->dataRepository->delete($datasetId, $rowId);
    }

    /**
     * Deletes a dataset.
     *
     * @param int $id Dataset ID to be deleted.
     */
    public function deleteDataset(int $id): void
    {
        $this->datasetRepository->setDeleted($id);
        // $this->columnRepository->deleteAllColumns($id);
    }

    public function getDataset(): Dataset
    {
        return $this->dataset;
    }

    public function getDatasetRepository(): DatasetRepository
    {
        return $this->datasetRepository;
    }

    public function getColumnRepository(): ColumnRepository
    {
        return $this->columnRepository;
    }

    public function getDataRepository(): DataRepository
    {
        return $this->dataRepository;
    }

    // public function getCreator(): DatasetCreator
    // {
    //     return new DatasetCreator(
    //         $this->datasetRepository,
    //         $this->columnRepository,
    //         $this->dataRepository
    //     );
    // }

    // public function getUpdater(): DatasetUpdater
    // {
    //     return new DatasetUpdater(
    //         $this->datasetRepository,
    //         $this->columnRepository,
    //         $this->dataRepository
    //     );
    // }
}
