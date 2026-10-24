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

class DatasetCreatorService
{
    private bool $lock = false;

    private ?Dataset $dataset = null;

    /** @var DatasetColumn[] $columns */
    private array $columns = [];

    public function __construct(
        readonly private DatasetRepositoryInterface $datasetRepository,
        readonly private DatasetDataRepositoryInterface $dataRepository,
        readonly private DatasetColumnRepositoryInterface $columnRepository,
    ) {
    }

    public function configure(
        string $name,
        string $slug = '',
        string $component = '',
        string $presenter = '',
        bool $active = true,
        bool $deleted = false
    ): self {
        if (!$this->lock) {
            $this->dataset = new Dataset(
                name: $name,
                slug: $slug,
                component: $component,
                presenter: $presenter,
                active: $active,
                deleted: $deleted,
            );
            $this->lock = true;
        }

        return $this;
    }

    public function loadDataset(int $datasetId): ?Dataset
    {
        if ($this->lock) {
            return null;
        }

        $this->dataset = $this->datasetRepository->findById($datasetId);
        $this->lock = true;

        return $this->dataset;
    }

    public function setDataset(Dataset $dataset): bool
    {
        if ($this->lock) {
            return false;
        }

        $this->dataset = $dataset;
        $this->lock = true;

        return true;
    }

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
}
