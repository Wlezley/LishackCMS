<?php

declare(strict_types=1);

namespace App\Models\Dataset\Repository;

use App\Models\Dataset\Entity\Dataset;
use App\Models\Helpers\SqlHelper;
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
            throw new \InvalidArgumentException('Cannot update dataset without ID.');
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
            ->where('dataset_id', $id)
            ->update(['deleted' => (int) $deleted]);
    }

    public function exists(int $id): bool
    {
        $result = $this->db->table(self::TABLE_NAME)
            ->where([
                'id' => $id,
                'deleted' => 0
            ])->fetch();

        return $result !== null;
    }
}
