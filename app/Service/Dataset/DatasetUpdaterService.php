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
use App\Models\Helpers\StringHelper;
use Webmozart\Assert\Assert;

class DatasetUpdaterService
{
    private ?Dataset $dataset = null;

    /** @var DatasetColumn[] $columns */
    private array $columns = [];

    public function __construct(
        readonly private DatasetRepositoryInterface $datasetRepository,
        readonly private DatasetDataRepositoryInterface $dataRepository,
        readonly private DatasetColumnRepositoryInterface $columnRepository,
    ) {
    }

    public function loadDatasetById(int $id): void
    {
        $this->dataset = $this->datasetRepository->findById($id);
        Assert::notNull($this->dataset, 'Dataset not found.');

        $this->columns = $this->columnRepository->findByDatasetId($this->dataset->getId());
    }

    /**
     * @throws DatasetException If the dataset has not loaded.
     */
    public function reconfigure(
        string $name,
        ?string $slug = null,
        ?string $component = null,
        ?string $presenter = null,
        bool $active = true,
        bool $deleted = false
    ): self {
        if ($this->dataset === null) {
            throw new DatasetException('Dataset is not loaded.');
        }

        $this->dataset
            ->setName($name)
            ->setSlug($slug)
            ->setComponent($component)
            ->setPresenter($presenter)
            ->setActive($active)
            ->setDeleted($deleted);

        return $this;
    }

    /**
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
        if ($this->dataset === null) {
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
        if ($this->dataset === null) {
            throw new DatasetException('Dataset is not loaded.');
        }

        if (!isset($this->columns[$columnId])) {
            return $this->addColumn($columnName, $slug, $type, $required, $listed, $hidden, $deleted, $defaultValue);
        }

        if ($slug === null || !StringHelper::isSlug($slug)) {
            $slug = StringHelper::slugize($columnName);
        }

        // TODO: DEBUG ONLY !!!
        bdump($columnId, 'COLUMN ID');
        bdump($this->columns, 'COLUMNS');

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

    public function getLastColumnId(): int
    {
        if (empty($this->columns)) {
            return 0;
        }

        return max(array_keys($this->columns));
    }

    /**
     * @throws DatasetException If the dataset is not configured or has no columns.
     */
    public function commit(): int
    {
        try {
            Assert::notEmpty($this->columns, 'Dataset must have at least one column.');
            Assert::notNull($this->dataset, 'Dataset must be configured.');

            $datasetId = $this->dataset->getId();
        } catch (\Throwable $e) {
            throw new DatasetException($e->getMessage(), 0, $e);
        }

        foreach ($this->columns as $column) {
            $column->setDataset($this->dataset);
        }

        $this->datasetRepository->save($this->dataset);
        $this->columnRepository->flush();

        $this->dataRepository->updateTable($datasetId, $this->columns);

        return $datasetId;
    }
}
