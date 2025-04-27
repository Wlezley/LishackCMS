<?php

declare(strict_types=1);

namespace App\Models\Dataset\Repository;

use App\Models\Dataset\DatasetException;
use App\Models\Dataset\Entity\Dataset;
use App\Models\Helpers\ArrayHelper;
use Nette\Database\Explorer;

final class DatasetRepository
{
    public const TABLE_NAME = 'dataset';

    public function __construct(
        private Explorer $db
    ) {}

    public function findById(int $id): ?Dataset
    {
        $row = $this->db->table(self::TABLE_NAME)->get($id);
        return $row ? Dataset::fromDatabaseRow($row->toArray()) : null;
    }

    public function findBySlug(string $slug): ?Dataset
    {
        $row = $this->db->table(self::TABLE_NAME)
            ->where('slug', $slug)
            ->fetch();

        return $row ? Dataset::fromDatabaseRow($row->toArray()) : null;
    }

    /** @return Dataset[] */
    public function findAll(): array
    {
        $result = [];
        foreach ($this->db->table(self::TABLE_NAME)->order('id') as $row) {
            $result[] = Dataset::fromDatabaseRow($row->toArray());
        }
        return $result;
    }

    public function insert(Dataset $dataset): Dataset
    {
        $row = $this->db->table(self::TABLE_NAME)->insert($dataset->toDatabaseRow());
        $dataset->id = (int) $row->getPrimary();
        return $dataset;
    }

    public function update(Dataset $dataset): void
    {
        if ($dataset->id === null) {
            throw new DatasetException('Cannot update dataset without ID.');
        }

        $this->db->table(self::TABLE_NAME)
            ->where('id', $dataset->id)
            ->update($dataset->toDatabaseRow());
    }

    public function delete(int $id): int
    {
        return $this->db->table(self::TABLE_NAME)
            ->where('id', $id)
            ->delete();
    }

    public function setDeleted(int $id, bool $deleted = true): int
    {
        return $this->db->table(self::TABLE_NAME)
            ->where('id', $id)
            ->update(['deleted' => (int) $deleted]);
    }

    public function exists(int $id, bool $includeDeleted = false): bool
    {
        $query = $this->db->table(self::TABLE_NAME)
            ->where('id', $id);

        if (!$includeDeleted) {
            $query->where('deleted', 0);
        }

        return $query->fetch() !== null;
    }

    /**
     * Retrieves a list of datasets with optional search and pagination.
     *
     * @param int $limit Number of results to return (default: 50).
     * @param int $offset Offset for pagination (default: 0).
     * @param string|null $search Optional search query for name or slug or component or presenter.
     * @return array<int|string,array<string,string|int|null>>|null Array of datasets indexed by its id, or null if empty.
     */
    public function getList(int $limit = 50, int $offset = 0, ?string $search = null): ?array
    {
        $query = $this->db->table(DatasetRepository::TABLE_NAME)
            ->where('deleted', 0)
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
        $query = $this->db->table(DatasetRepository::TABLE_NAME)
            ->where('deleted', 0);

        if ($search !== null) {
            $query->where('name LIKE ? OR slug LIKE ? OR component LIKE ? OR presenter LIKE ?', "%$search%", "%$search%", "%$search%", "%$search%");
        }

        return $query->count('*');
    }
}
