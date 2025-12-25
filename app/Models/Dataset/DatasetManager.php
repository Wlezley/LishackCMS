<?php

declare(strict_types=1);

namespace App\Models\Dataset;

use App\Models\Dataset\Entity\Dataset;
use App\Models\Dataset\Entity\DatasetColumn;
use App\Models\Dataset\Repository\ColumnRepository;
use App\Models\Dataset\Repository\DataRepository;
use App\Models\Dataset\Repository\DatasetRepository;
use Webmozart\Assert\Assert;

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

        $dataset = $this->datasetRepository->findById($id);
        Assert::notNull($dataset, 'Dataset must not be null.');
        $this->dataset = $dataset;
        Assert::notNull($this->dataset->id, 'Dataset ID must not be null.');

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
    public function getColumns(): array
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

        foreach ($this->columns as $column) {
            Assert::notNull($column->columnId, 'Column ID must not be null.');

            $columnList[$column->columnId] = [
                'columnId' => $column->columnId,
                'name' => $column->name,
                'slug' => $column->slug,
                'type' => $column->type,
                'required' => $column->required,
                'listed' => $column->listed,
                'hidden' => $column->hidden,
                'deleted' => $column->deleted,
                'default' => $column->default,
            ];
        }

        return $columnList;
    }

    /** @return array<int,mixed> */
    public function getListedColumns(): array
    {
        if (empty($this->columns)) {
            return [];
        }

        $columnList = [];

        foreach ($this->columns as $column) {
            if (!$column->listed) {
                continue;
            }

            Assert::notNull($column->columnId, 'Column ID must not be null.');

            $columnList[$column->columnId] = [
                'columnId' => $column->columnId,
                'key' => "data_{$column->columnId}",
                'name' => $column->name,
                'slug' => $column->slug,
                'type' => $column->type,
                'required' => $column->required,
                'listed' => $column->listed,
                'hidden' => $column->hidden,
                'deleted' => $column->deleted,
                'default' => $column->default,
            ];
        }

        return $columnList;
    }

    public function getLastColumnId(): int
    {
        if (empty($this->columns)) {
            return 0;
        }

        $columnList = $this->getColumnsList();
        Assert::notEmpty($columnList, 'Dataset must have at least one column.');

        return (int) max(array_keys($columnList));
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
    }

    public function getDatasetId(): ?int
    {
        return $this->dataset->id;
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
}
