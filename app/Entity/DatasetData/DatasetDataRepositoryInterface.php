<?php

declare(strict_types=1);

namespace App\Entity\DatasetData;

interface DatasetDataRepositoryInterface
{
    /**
     * @return array<string, mixed>|null
     * @throws \Doctrine\DBAL\Exception
     */
    public function findById(int $datasetId, int $id): ?array;

    /**
     * @return array<int, array<string, mixed>>|null
     * @throws \Doctrine\DBAL\Exception
     */
    public function getList(int $datasetId, ?int $limit = 50, ?int $offset = 0, ?string $search = null): ?array;

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function getCount(int $datasetId, ?string $search = null): int;

    /**
     * @param array<string, mixed> $data
     * @throws \Doctrine\DBAL\Exception
     */
    public function insert(int $datasetId, array $data): int;

    /**
     * @param array<string, mixed> $data
     * @throws \Doctrine\DBAL\Exception
     */
    public function update(int $datasetId, int $id, array $data): void;

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function delete(int $datasetId, int $id): void;

    /**
     * @param \App\Entity\DatasetColumn\DatasetColumn[] $columns
     * @throws \Doctrine\DBAL\Exception
     */
    public function createTable(int $datasetId, array $columns): void;

    /**
     * @param \App\Entity\DatasetColumn\DatasetColumn[] $columns
     * @throws \Doctrine\DBAL\Exception
     * @throws \Doctrine\DBAL\Types\Exception\TypesException
     */
    public function updateTable(int $datasetId, array $columns): void;
}
