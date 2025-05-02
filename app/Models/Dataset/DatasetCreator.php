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
    private ?Dataset $dataset = null;

    /** @var DatasetColumn[] $columns */
    private array $columns = [];

    public function __construct(
        private DatasetRepository $datasetRepository,
        private ColumnRepository $columnRepository,
        private DataRepository $dataRepository
    ) {}

    /**
     * Configures the base dataset metadata.
     *
     * This method must be called before committing the dataset.
     *
     * @param string $name Name of the dataset.
     * @param string $slug Optional slug identifier.
     * @param string $component Optional frontend component binding.
     * @param string $presenter Optional presenter routing value.
     * @param bool $active Whether the dataset is active.
     * @return self
     */
    public function configure(string $name, string $slug = '', string $component = '', string $presenter = '', bool $active = true, bool $deleted = false): self
    {
        $this->dataset = (new Dataset())
            ->setId(null)
            ->setName($name)
            ->setSlug($slug)
            ->setComponent($component)
            ->setPresenter($presenter)
            ->setActive($active)
            ->setDeleted($deleted);

        $this->dataset->prepare();
        $this->dataset->validate();

        return $this;
    }

    /**
     * Adds a column to the dataset definition.
     *
     * @param string $name Column display name.
     * @param string $slug Optional slug identifier.
     * @param string $type Column data type (e.g., 'string', 'int').
     * @param bool $required The column is required.
     * @param bool $listed The column is listed in DataList.
     * @param bool $hidden The column is editable only with an user in the admin role.
     * @param bool $deleted The column is marked as deleted.
     * @param string|null $default Default value of the column.
     * @return self
     */
    public function addColumn(
        string $name,
        string $slug = '',
        string $type = 'string',
        bool $required = false,
        bool $listed = false,
        bool $hidden = false,
        bool $deleted = false,
        ?string $default = null
    ): self
    {
        $column = (new DatasetColumn())
            ->setName($name)
            ->setSlug($slug)
            ->setType($type)
            ->setRequired($required)
            ->setListed($listed)
            ->setHidden($hidden)
            ->setDeleted($deleted)
            ->setDefault($default);

        $column->prepare();
        $column->validate();

        $this->columns[] = $column;

        return $this;
    }

    /**
     * Finalizes and commits the dataset to the database.
     *
     * This will insert dataset metadata, store column definitions,
     * and create a dedicated table for data storage.
     *
     * @throws DatasetException If dataset is not configured or has no columns.
     * @return int The ID of the created dataset.
     */
    public function commit(): int
    {
        if (!isset($this->dataset)) {
            throw new DatasetException('Dataset is not configured yet.');
        }

        if (empty($this->columns)) {
            throw new DatasetException('Dataset must have at least one column.');
        }

        $this->dataset = $this->datasetRepository->insert($this->dataset);
        $columnId = 0;

        /** @var DatasetColumn $column */
        foreach ($this->columns as $column) {
            $column->setDatasetId($this->dataset->id);
            $column->setColumnId(++$columnId);
            $this->columnRepository->insert($column);
        }

        $this->dataRepository->createTable($this->dataset->id, $this->columns);

        return $this->dataset->id;
    }

    /**
     * Resets the internal state of the creator for reuse.
     *
     * Clears both dataset config and column list.
     */
    public function reset(): void
    {
        $this->dataset = null;
        $this->columns = [];
    }

    /**
     * Returns the configured dataset object.
     *
     * @throws DatasetException If dataset has not been configured yet.
     * @return Dataset
     */
    public function getDataset(): Dataset
    {
        if (!$this->dataset) {
            throw new DatasetException('Dataset is not configured yet.');
        }

        return $this->dataset;
    }

    /**
     * Returns all columns added to the dataset.
     *
     * @return DatasetColumn[]
     */
    public function getColumns(): array
    {
        return $this->columns;
    }
}
