<?php

declare(strict_types=1);

namespace App\Models\Dataset;

use App\Models\Dataset\Entity\Dataset;
use App\Models\Dataset\Entity\DatasetColumn;
use App\Models\Dataset\Repository\ColumnRepository;
use App\Models\Dataset\Repository\DataRepository;
use App\Models\Dataset\Repository\DatasetRepository;

class DatasetCreator
{
    private Dataset $dataset;

    /** @var DatasetColumn[] $columns */
    private array $columns;

    public function __construct(
        private DatasetRepository $datasetRepository,
        private ColumnRepository $columnRepository,
        private DataRepository $dataRepository
    ) {}

    public function configure(string $name, string $slug = '', string $component = '', string $presenter = '', bool $active = true): self
    {
        $this->dataset = (new Dataset())
            ->setId(null)
            ->setName($name)
            ->setSlug($slug)
            ->setComponent($component)
            ->setPresenter($presenter)
            ->setActive($active);

        $this->dataset->prepare();
        $this->dataset->validate();

        return $this;
    }

    public function addColumn(string $name, string $slug = '', string $type = 'string', bool $required = false): self
    {
        $column = (new DatasetColumn())
            ->setName($name)
            ->setSlug($slug)
            ->setType($type)
            ->setRequired($required);

        $column->prepare();
        $column->validate();

        $this->columns[] = $column;

        return $this;
    }

    public function commit(): int
    {
        // STEP 1: Create DATASET index to get DATASET ID
        $this->dataset = $this->datasetRepository->insert($this->dataset);

        // STEP 2: Create COLUMN CONFIG
        $columnId = 0;
        /** @var DatasetColumn $column */
        foreach ($this->columns as $column) {
            $column->setDatasetId($this->dataset->id);
            $column->setColumnId(++$columnId);
            $this->columnRepository->insert($column);
        }

        // STEP 3: Create TABLE for DATASET
        $this->dataRepository->createTable($this->dataset->id, $this->columns);

        // RETURN: ID of the new DATASET
        return $this->dataset->id;
    }

    public function getDataset(): Dataset
    {
        return $this->dataset;
    }

    /** @return DatasetColumn[] */
    public function getColumns(): array
    {
        return $this->columns;
    }
}
