<?php

declare(strict_types=1);

namespace App\Models\Dataset;

use App\Models\Dataset\Entity\Dataset;
use App\Models\Dataset\Entity\DatasetColumn;
use App\Models\Dataset\Entity\DatasetRow;
use App\Models\Dataset\Repository\ColumnRepository;
use App\Models\Dataset\Repository\DataRepository;
use App\Models\Dataset\Repository\DatasetRepository;
use App\Models\Helpers\ArrayHelper;
use Nette\Database\Explorer;

final class DatasetManager
{
    private Dataset $dataset;

    /** @var DatasetColumn[] $columns */
    private array $columns;

    public function __construct(
        private DatasetRepository $datasetRepository,
        private ColumnRepository $columnRepository,
        private DataRepository $dataRepository,
        private Explorer $db
    ) {}

    public function loadDatasetById(int $id): bool
    {
        if (!$this->datasetRepository->exists($id)) {
            return false;
        }

        $this->dataset = $this->datasetRepository->findById($id);
        $this->columns = $this->columnRepository->findByDatasetId($this->dataset->id);

        return true;
    }

    /** @return DatasetColumn[] */
    public function getColumnSchema(): array
    {
        return $this->columns;
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
        $this->datasetRepository->setDeleted($id, true);
        $this->columnRepository->deleteAllColumns($id);
    }

    public function getList(int $limit = 50, int $offset = 0, ?string $search = null): ?array
    {
        $query = $this->db->table(DatasetRepository::TABLE_NAME)
            ->limit($limit, $offset)
            ->order('id ASC');

        if ($search !== null) {
            $query->where('name LIKE ? OR slug LIKE ? OR component LIKE ? OR presenter LIKE ?', "%$search%", "%$search%", "%$search%", "%$search%");
        }

        $data = $query->fetchAll();

        return $data ? ArrayHelper::resultToArray($data) : null;
    }

    public function getCount(?string $search = null): int
    {
        $query = $this->db->table(DatasetRepository::TABLE_NAME);

        if ($search !== null) {
            $query->where('name LIKE ? OR slug LIKE ? OR component LIKE ? OR presenter LIKE ?', "%$search%", "%$search%", "%$search%", "%$search%");
        }

        return $query->count('*');
    }
}
