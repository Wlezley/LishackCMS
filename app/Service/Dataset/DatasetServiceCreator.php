<?php

declare(strict_types=1);

namespace App\Service\Dataset;

use App\Entity\Dataset\Dataset;
use App\Entity\Dataset\DatasetRepositoryInterface;
use App\Entity\DatasetColumn\DatasetColumn;
use App\Entity\DatasetColumn\DatasetColumnRepositoryInterface;
use App\Entity\DatasetData\DatasetDataRepositoryInterface;
use App\Enum\Dataset\DatasetColumnType;
use App\Exception\DatasetException;
use Webmozart\Assert\Assert;

class DatasetServiceCreator
{
    private ?Dataset $dataset = null;

    /** @var DatasetColumn[] $columns */
    private array $columns = [];

    public function __construct(
        private DatasetRepositoryInterface $datasetRepository,
        private DatasetColumnRepositoryInterface $columnRepository,
        private DatasetDataRepositoryInterface $dataRepository,
    ) {
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
     * @param bool $deleted Whether the dataset is marked as deleted.
     * @return DatasetServiceCreator
     */
    public function configure(
        string $name,
        string $slug = '',
        string $component = '',
        string $presenter = '',
        bool $active = true,
        bool $deleted = false
    ): self {
        $this->dataset = new Dataset(
            name: $name,
            slug: $slug,
            component: $component,
            presenter: $presenter,
            active: $active,
            deleted: $deleted,
        );

        return $this;
    }

    /**
     * Adds a column to the dataset definition.
     *
     * @param string $name Column display name.
     * @param string $slug Optional slug identifier.
     * @param DatasetColumnType $type Column data type (e.g., 'string', 'int').
     * @param bool $required The column is required.
     * @param bool $listed The column is listed in the DataList.
     * @param bool $hidden The column is editable only with a user in the admin role.
     * @param bool $deleted The column is marked as deleted.
     * @param string|null $default Default value of the column.
     */
    public function addColumn(
        string $name,
        string $slug = '',
        DatasetColumnType $type = DatasetColumnType::String,
        bool $required = false,
        bool $listed = false,
        bool $hidden = false,
        bool $deleted = false,
        ?string $default = null
    ): self {
        Assert::notNull($this->dataset, 'Dataset must be configured before adding columns.');

        $column = new DatasetColumn(
            dataset: $this->dataset,
            columnId: count($this->columns) > 0 ? $this->columns[count($this->columns) - 1]->getColumnId() + 1 : 1,
            name: $name,
            slug: $slug,
            type: $type,
            required: $required,
            listed: $listed,
            hidden: $hidden,
            deleted: $deleted,
            defaultValue: $default,
        );

        $this->columns[] = $column;

        return $this;
    }

    /**
     * Finalizes and commits the dataset to the database.
     *
     * This will insert dataset metadata, store column definitions,
     * and create a dedicated table for data storage.
     *
     * @return int The ID of the created dataset.
     * @throws DatasetException If the dataset is not configured or has no columns.
     */
    public function commit(): int
    {
        if ($this->dataset === null) {
            throw new DatasetException('Dataset is not configured yet.');
        }

        if (empty($this->columns)) {
            throw new DatasetException('Dataset must have at least one column.');
        }

        $this->datasetRepository->save($this->dataset);
        $datasetId = $this->dataset->getId();

        $columnId = 0;
        foreach ($this->columns as $column) {
            $column->setDataset($this->dataset);
            $column->setColumnId(++$columnId);
            $this->columnRepository->save($column);
        }

        $this->dataRepository->createTable($datasetId, $this->columns); // TODO: We need method to create table for DATA !!!

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
     * @throws DatasetException If the dataset has not been configured yet.
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
