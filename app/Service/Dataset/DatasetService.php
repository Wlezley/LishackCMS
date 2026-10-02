<?php

declare(strict_types=1);

namespace App\Service\Dataset;

use App\Entity\Dataset\Dataset;
use App\Entity\Dataset\DatasetRepositoryInterface;
use App\Entity\DatasetColumn\DatasetColumn;
use App\Entity\DatasetColumn\DatasetColumnRepositoryInterface;
use App\Entity\DatasetData\DatasetDataRepositoryInterface;
use App\Exception\DatasetException;

/**
 * This is the head service to control the DATASET behavior.
 * The main purpose of this class is to provide a single point of access to the dataset repository.
 * It also provides methods to manage the dataset columns and data.
 *
 * 0. DatasetService: Provides a unified interface for dataset operations.
 * 1. DatasetServiceCreator: Creates a new dataset and its columns.
 * 2. DatasetServiceUpdater: Updates an existing dataset and its columns.
 * 3. DatasetServiceDeleter: Marks a dataset as deleted.
 * 4. DatasetServiceLoader: Loads a dataset and its columns to the CACHED_DATASET.
 * 5. DatasetServiceExporter: Exports a dataset and its columns to a CSV file.
 * 6. DatasetServiceImporter: Imports a dataset and its columns from a CSV file.
 * 7. DatasetServiceHandler: Handles dataset-related operations (e.g., deletion, export, import).
 * 8. DatasetServiceManager: Manages the lifecycle of datasets and their columns. XXX
 * 9. DatasetServiceFactory: Creates instances of DatasetService.
 * 10. DatasetServiceConfigurator: Configures the dataset service.
 *
 * These classes must be final, as they are used as services in the Nette DI container.
 * They should not be extended or mocked.
 */

class DatasetService
{
    private Dataset $dataset;

    /** @var DatasetColumn[] */
    private array $datasetColumns = [];

    public function __construct(
        private DatasetRepositoryInterface $datasetRepository,
        private DatasetColumnRepositoryInterface $datasetColumnRepository,
        private DatasetDataRepositoryInterface $datasetDataRepository,
        public DatasetServiceCreator $creator,
        public DatasetServiceUpdater $updater,
    ) {
    }

    /**
     * @throws DatasetException
     */
    public function loadDatasetById(int $id, bool $includeDeleted = false): bool
    {
        $dataset = $this->datasetRepository->findById($id);
        if ($dataset === null || (!$includeDeleted && $dataset->isDeleted())) {
            return false;
        }
        $this->dataset = $dataset;

        $columns = $this->datasetColumnRepository->findByDatasetId($id, $includeDeleted);
        $this->datasetColumns = $columns;

        return true;
    }

    public function isReady(): bool
    {
        return isset($this->dataset) && !empty($this->datasetColumns);
    }

    public function getDataset(): Dataset
    {
        if (!isset($this->dataset)) {
            throw new \LogicException('Dataset not loaded. Call loadDatasetById() first.');
        }
        return $this->dataset;
    }

    /** @return DatasetColumn[] */
    public function getColumns(): array
    {
        return $this->datasetColumns;
    }

    /** @return array<int,mixed> */
    public function getColumnsList(): array
    {
        $columnList = [];
        foreach ($this->datasetColumns as $column) {
            $columnList[$column->getId()] = [
                'columnId' => $column->getId(),
                'name' => $column->getName(),
                'slug' => $column->getSlug(),
                'type' => $column->getType()->value,
                'required' => $column->isRequired(),
                'listed' => $column->isListed(),
                'hidden' => $column->isHidden(),
                'deleted' => $column->isDeleted(),
                'default' => $column->getDefaultValue(),
            ];
        }
        return $columnList;
    }

    /** @return array<int,mixed> */
    public function getListedColumns(): array
    {
        $columnList = [];
        foreach ($this->datasetColumns as $column) {
            if (!$column->isListed()) {
                continue;
            }
            $columnList[$column->getId()] = [
                'columnId' => $column->getId(),
                'key' => "data_{$column->getId()}",
                'name' => $column->getName(),
                'slug' => $column->getSlug(),
                'type' => $column->getType()->value,
                'required' => $column->isRequired(),
                'listed' => $column->isListed(),
                'hidden' => $column->isHidden(),
                'deleted' => $column->isDeleted(),
                'default' => $column->getDefaultValue(),
            ];
        }
        return $columnList;
    }

    public function deleteRow(int $datasetId, int $rowId): void
    {
        $this->datasetDataRepository->delete($datasetId, $rowId);
    }

    public function deleteDatasetById(int $id): void
    {
        $dataset = $this->datasetRepository->findById($id);
        if ($dataset !== null) {
            $dataset->setDeleted(true);
            $this->datasetRepository->save($dataset);
        }
    }

    public function getDataRepository(): DatasetDataRepositoryInterface
    {
        return $this->datasetDataRepository;
    }

    public function getColumnRepository(): DatasetColumnRepositoryInterface
    {
        return $this->datasetColumnRepository;
    }

    public function existsById(int $id, bool $includeDeleted = false): bool
    {
        $criteria = ['id' => $id];

        if (!$includeDeleted) {
            $criteria['deleted'] = false;
        }

        return $this->datasetRepository->exists($criteria);
    }

    public function setDeleted(Dataset $dataset, bool $isDeleted): void
    {
        $dataset->setDeleted($isDeleted);
        $this->datasetRepository->save($dataset);
    }

    /**
     * Retrieves a list of datasets for use in the sidebar.
     *
     * Returns all datasets ordered by ID. By default, deleted datasets are excluded,
     * but this can be overridden via the `$includeDeleted` flag.
     *
     * @param bool $includeDeleted Whether to include datasets marked as deleted (default: false).
     * @return array<int, array<string, int|string>> Array of datasets indexed by ID, or null if none found.
     */
    public function getSidebarList(bool $includeDeleted = false): array
    {
        $datasets = $this->datasetRepository->findBySearch(
            includeDeleted: $includeDeleted,
        );

        $result = [];
        foreach ($datasets as $entity) {
            $result[$entity->getId()] = [
                'id' => $entity->getId(),
                'name' => $entity->getName(),
                'slug' => $entity->getSlug(),
            ];
        }

        return $result;
    }

    /**
     * Retrieves a list of datasets with optional search and pagination.
     *
     * @param int<0, max>|null $limit Number of results to return (default: 50).
     * @param int<0, max>|null $offset Offset for pagination (default: 0).
     * @param string|null $search Optional search query for name, slug, component, or presenter fields.
     * @return array<int,array<string,int|string|bool|null>>|null Array of datasets indexed by ID, or null if none found.
     */
    public function getList(?int $limit = 50, ?int $offset = 0, ?string $search = null): ?array
    {
        $datasets = $this->datasetRepository->findBySearch($limit, $offset, false, $search);

        if (!$datasets) {
            return null;
        }

        $result = [];
        foreach ($datasets as $dataset) {
            $result[$dataset->getId()] = [
                'id' => $dataset->getId(),
                'name' => $dataset->getName(),
                'slug' => $dataset->getSlug(),
                'component' => $dataset->getComponent(),
                'presenter' => $dataset->getPresenter(),
                'deleted' => $dataset->isDeleted(),
            ];
        }

        return $result;
    }

    public function getCount(?string $search = null, bool $includeDeleted = false): int
    {
        return $this->datasetRepository->countBySearch($search, $includeDeleted);
    }

    public function getLastColumnId(): int
    {
        $maxId = 0;
        foreach ($this->datasetColumns as $column) {
            if ($column->getId() > $maxId) {
                $maxId = $column->getId();
            }
        }
        return $maxId;
    }
}
