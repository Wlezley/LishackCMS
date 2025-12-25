<?php

declare(strict_types=1);

namespace App\Models\Dataset;

use App\Models\Dataset\Entity\Dataset;
use App\Models\Dataset\Entity\DatasetColumn;
use App\Models\Dataset\Repository\ColumnRepository;
use App\Models\Dataset\Repository\DataRepository;
use App\Models\Dataset\Repository\DatasetRepository;
use Webmozart\Assert\Assert;

class DatasetUpdater
{
    private ?Dataset $dataset = null;

    /** @var DatasetColumn[] $columns */
    private array $columns = [];

    public function __construct(
        private DatasetRepository $datasetRepository,
        private ColumnRepository $columnRepository,
        private DataRepository $dataRepository
    ) {
    }

    public function loadDatasetById(int $id): bool
    {
        if (!$this->datasetRepository->exists($id, true)) {
            return false;
        }

        $this->dataset = $this->datasetRepository->findById($id);
        Assert::notNull($this->dataset, 'Dataset must not be null.');
        Assert::notNull($this->dataset->id, 'Dataset ID must not be null.');

        $this->columns = $this->columnRepository->findByDatasetId($this->dataset->id, true);

        return true;
    }

    /** @todo Rename to isLoaded(); because isReady() may be check method for operations before commit(). */
    public function isReady(): bool
    {
        if (!isset($this->dataset) || $this->dataset->id === null || empty($this->columns)) {
            return false;
        }

        return true;
    }

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
     *
     * @throws DatasetException If the dataset has not loaded.
     */
    public function configure(
        string $name,
        string $slug = '',
        string $component = '',
        string $presenter = '',
        bool $active = true,
        bool $deleted = false
    ): self {
        if (!isset($this->dataset)) {
            throw new DatasetException('Dataset is not loaded.');
        }

        $this->dataset
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
     * @param bool $listed The column is listed in the DataList.
     * @param bool $hidden The column is editable only with a user in the admin role.
     * @param bool $deleted The column is marked as deleted.
     * @param string|null $default Default value of the column.
     *
     * @throws DatasetException If the dataset has not loaded.
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
    ): self {
        if (!isset($this->dataset)) {
            throw new DatasetException('Dataset is not loaded.');
        }

        $columnId = $this->getLastColumnId() + 1;

        $column = (new DatasetColumn())
            ->setColumnId($columnId)
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

        $this->columns[$columnId] = $column;

        return $this;
    }

    /**
     * Update a column definition in the dataset by column ID.
     *
     * @param int $columnId Column ID.
     * @param string $name Column display name.
     * @param string $slug Optional slug identifier.
     * @param string $type Column data type (e.g., 'string', 'int').
     * @param bool $required The column is required.
     * @param bool $listed The column is listed in the DataList.
     * @param bool $hidden The column is editable only with a user in the admin role.
     * @param bool $deleted The column is marked as deleted.
     * @param string|null $default Default value of the column.
     *
     * @throws DatasetException If the dataset has not loaded.
     */
    public function updateColumn(
        int $columnId,
        string $name,
        string $slug = '',
        string $type = 'string',
        bool $required = false,
        bool $listed = false,
        bool $hidden = false,
        bool $deleted = false,
        ?string $default = null
    ): self {
        if (!isset($this->dataset)) {
            throw new DatasetException('Dataset is not loaded.');
        }

        if (!isset($this->columns[$columnId])) {
            return $this->addColumn($name, $slug, $type, $required, $listed, $hidden, $deleted, $default);
        }

        $this->columns[$columnId]
            ->setName($name)
            ->setSlug($slug)
            ->setType($type)
            ->setRequired($required)
            ->setListed($listed)
            ->setHidden($hidden)
            ->setDeleted($deleted)
            ->setDefault($default);

        $this->columns[$columnId]->prepare();
        $this->columns[$columnId]->validate();

        return $this;
    }

    /**
     * Returns last column ID or '0' if the columns array is empty.
     *
     * @return int The ID of the last column.
     */
    public function getLastColumnId(): int
    {
        if (empty($this->columns)) {
            return 0;
        }

        return max(array_keys($this->columns));
    }

    /**
     * Finalizes and commits the dataset to the database.
     *
     * This will insert dataset metadata, store column definitions,
     * and create a dedicated table for data storage.
     *
     * @throws DatasetException If the dataset is not configured or has no columns.
     * @return int The ID of the created dataset.
     */
    public function commit(): int
    {
        try {
            Assert::notEmpty($this->columns, 'Dataset must have at least one column.');
            Assert::notNull($this->dataset, 'Dataset must be configured.');

            $datasetId = $this->dataset->id;
            Assert::notNull($datasetId, 'Dataset ID must not be null.');
        } catch (\Throwable $e) {
            throw new DatasetException($e->getMessage(), 0, $e);
        }

        $this->datasetRepository->update($this->dataset);

        foreach ($this->columns as $column) {
            if ($column->datasetId == 0) {
                $column->setDatasetId($datasetId);
                $this->columnRepository->insert($column);
            } else {
                $this->columnRepository->update($column);
            }
        }

        $this->dataRepository->updateTable($datasetId, $this->columns);

        return $datasetId;
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
     * @throws DatasetException If dataset has not loaded.
     */
    public function getDataset(): Dataset
    {
        if (!$this->dataset) {
            throw new DatasetException('Dataset is not loaded.');
        }

        return $this->dataset;
    }

    /**
     * Returns all columns of the dataset, including changes and new columns.
     *
     * @return DatasetColumn[]
     */
    public function getColumns(): array
    {
        return $this->columns;
    }
}
