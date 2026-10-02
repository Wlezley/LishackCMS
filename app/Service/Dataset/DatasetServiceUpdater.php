<?php

declare(strict_types=1);

namespace App\Service\Dataset;

//use App\Models\Dataset\Assert;
//use App\Models\Dataset\ColumnRepository;
//use App\Models\Dataset\DataRepository;
//use App\Models\Dataset\Dataset;
//use App\Models\Dataset\DatasetColumn;
//use App\Models\Dataset\DatasetException;
//use App\Models\Dataset\DatasetRepositoryInterface;
//use App\Models\Dataset\DatasetService;

use App\Entity\Dataset\Dataset;
use App\Entity\Dataset\DatasetRepositoryInterface;
use App\Entity\DatasetColumn\DatasetColumn;
use App\Entity\DatasetColumn\DatasetColumnRepositoryInterface;
use App\Entity\DatasetData\DatasetDataRepositoryInterface;
use App\Enum\Dataset\DatasetColumnType;
use App\Exception\DatasetException;
use App\Models\Helpers\StringHelper;
use Webmozart\Assert\Assert;

class DatasetServiceUpdater
{
    private ?Dataset $dataset = null;

    /** @var DatasetColumn[] $columns */
    private array $columns = [];

    public function __construct(
        private DatasetRepositoryInterface $datasetRepository,
        private DatasetDataRepositoryInterface $dataRepository,
        private DatasetColumnRepositoryInterface $columnRepository,
    ) {
    }

    public function loadDatasetById(int $id): bool
    {
        $criteria = ['id' => $id, 'deleted' => false];
        if (!$this->datasetRepository->exists($criteria)) {
            return false;
        }

        $this->dataset = $this->datasetRepository->findById($id);
        Assert::notNull($this->dataset, 'Dataset must not be null.');

        $this->columns = $this->columnRepository->findByDatasetId($this->dataset->getId());

        return true;
    }

    /** @todo Rename to isLoaded(); because isReady() may be check method for operations before commit(). */
    public function isReady(): bool
    {
        if (!isset($this->dataset) || empty($this->columns)) {
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

        // TODO: WTF ???
//        $this->dataset->prepare();
//        $this->dataset->validate();

        return $this;
    }

    /**
     * Adds a column to the dataset definition.
     *
     * @param string $columnName Column display name.
     * @param string|null $slug Optional slug identifier.
     * @param DatasetColumnType $type Column data type (e.g., 'string', 'int').
     * @param bool $required The column is required.
     * @param bool $listed The column is listed in the DataList.
     * @param bool $hidden The column is editable only with a user in the admin role.
     * @param bool $deleted The column is marked as deleted.
     * @param string|null $defaultValue Default value of the column.
     *
     * @throws DatasetException If the dataset has not loaded.
     */
    public function addColumn(
        string $columnName,
        ?string $slug = null,
        DatasetColumnType $type = DatasetColumnType::String,
        bool $required = false,
        bool $listed = false,
        bool $hidden = false,
        bool $deleted = false,
        ?string $defaultValue = null
    ): self {
        if (!isset($this->dataset)) {
            throw new DatasetException('Dataset is not loaded.');
        }

        $columnId = $this->getLastColumnId() + 1;

        if ($slug === null || !StringHelper::isSlug($slug)) {
            $slug = StringHelper::slugize($columnName);
        }

        $column = new DatasetColumn(
            dataset: $this->dataset,
            columnId: $columnId,
            name: $columnName,
            slug: $slug,
            type: $type,
            required: $required,
            listed: $listed,
            hidden: $hidden,
            deleted: $deleted,
            defaultValue: $defaultValue,
        );

        $this->columns[$columnId] = $column;

        return $this;
    }

    /**
     * Update a column definition in the dataset by column ID.
     *
     * @param int $columnId Column ID.
     * @param string $columnName Column display name.
     * @param string|null $slug Optional slug identifier.
     * @param DatasetColumnType $type Column data type (e.g., 'string', 'int').
     * @param bool $required The column is required.
     * @param bool $listed The column is listed in the DataList.
     * @param bool $hidden The column is editable only with a user in the admin role.
     * @param bool $deleted The column is marked as deleted.
     * @param string|null $defaultValue Default value of the column.
     *
     * @throws DatasetException If the dataset has not loaded.
     */
    public function updateColumn(
        int $columnId,
        string $columnName,
        ?string $slug = null,
        DatasetColumnType $type = DatasetColumnType::String,
        bool $required = false,
        bool $listed = false,
        bool $hidden = false,
        bool $deleted = false,
        ?string $defaultValue = null
    ): self {
        if (!isset($this->dataset)) {
            throw new DatasetException('Dataset is not loaded.');
        }

        if (!isset($this->columns[$columnId])) {
            return $this->addColumn($columnName, $slug, $type, $required, $listed, $hidden, $deleted, $defaultValue);
        }

        if ($slug === null || !StringHelper::isSlug($slug)) {
            $slug = StringHelper::slugize($columnName);
        }

        $this->columns[$columnId]
            ->setName($columnName)
            ->setSlug($slug)
            ->setType($type)
            ->setRequired($required)
            ->setListed($listed)
            ->setHidden($hidden)
            ->setDeleted($deleted)
            ->setDefaultValue($defaultValue);

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

            Assert::notNull($this->dataset, 'Dataset must not be null.');
            $datasetId = $this->dataset->getId();
        } catch (\Throwable $e) {
            throw new DatasetException($e->getMessage(), 0, $e);
        }

        $this->datasetRepository->save($this->dataset);

        foreach ($this->columns as $column) {
            $column->setDataset($this->dataset);
            $this->columnRepository->save($column);
        }

        // TODO...
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
     * @throws DatasetException If Dataset has not loaded.
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
