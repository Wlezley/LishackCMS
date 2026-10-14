<?php

declare(strict_types=1);

namespace App\Entity\DatasetData;

use App\Entity\DatasetColumn\DatasetColumn;
use App\Entity\DatasetColumn\DatasetColumnRepositoryInterface;
use App\Enum\SortOrderEnum;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Exception\TypesException;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Webmozart\Assert\Assert;

final readonly class DatasetDataRepository implements DatasetDataRepositoryInterface
{
    public const TABLE_NAME_PREFIX = 'dataset_data_';
    public const DATA_COLUMN_PREFIX = 'data_';

    public function __construct(
        private EntityManagerInterface $entityManager,
        private DatasetColumnRepositoryInterface $columnRepository,
    ) {
    }

    private function getTableName(int $datasetId): string
    {
        return self::TABLE_NAME_PREFIX . $datasetId;
    }

    /**
     * @return array<string, mixed>|null
     * @throws \Doctrine\DBAL\Exception
     */
    public function findById(int $datasetId, int $id): ?array
    {
        $connection = $this->entityManager->getConnection();
        $tableName = $this->getTableName($datasetId);
        $qb = $connection->createQueryBuilder();

        $qb->select('*')
            ->from($tableName, 'd')
            ->where('id = :id')
            ->setParameter('id', $id);

        $result = $qb->executeQuery()->fetchAssociative();

        return $result ?: null;
    }

    /**
     * @return array<int, array<string, mixed>>|null
     * @throws \Doctrine\DBAL\Exception
     */
    public function getList(int $datasetId, ?int $limit = 50, ?int $offset = 0, ?string $search = null): ?array
    {
        $connection = $this->entityManager->getConnection();
        $tableName = $this->getTableName($datasetId);
        $qb = $connection->createQueryBuilder();

        $qb->select('*')
            ->from($tableName, 'd')
            ->orderBy('id', SortOrderEnum::Asc->value);

        if ($limit !== null) {
            $qb->setMaxResults($limit);
        }

        if ($offset !== null) {
            $qb->setFirstResult($offset);
        }

        if ($search !== null) {
            $columns = $this->columnRepository->findByDatasetId($datasetId);
            foreach ($columns as $column) {
                // Assuming we only search in string-like columns or all data_ columns
                $columnName = self::DATA_COLUMN_PREFIX . $column->getColumnId();
                $qb->orWhere($columnName . ' LIKE :search');
            }
            $qb->setParameter('search', '%' . $search . '%');
        }

        $data = $qb->executeQuery()->fetchAllAssociative();

        if (!$data) {
            return null;
        }

        $result = [];
        foreach ($data as $row) {
            $result[$row['id']] = $row;
        }

        return $result;
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function getCount(int $datasetId, ?string $search = null): int
    {
        $connection = $this->entityManager->getConnection();
        $tableName = $this->getTableName($datasetId);
        $qb = $connection->createQueryBuilder();

        $qb->select('COUNT(*)')
            ->from($tableName, 'd');

        if ($search !== null) {
            $columns = $this->columnRepository->findByDatasetId($datasetId);
            foreach ($columns as $column) {
                $columnName = self::DATA_COLUMN_PREFIX . $column->getColumnId();
                $qb->orWhere($columnName . ' LIKE :search');
            }
            $qb->setParameter('search', '%' . $search . '%');
        }

        return (int) $qb->executeQuery()->fetchOne();
    }

    /**
     * @param array<string, mixed> $data
     * @throws \Doctrine\DBAL\Exception
     */
    public function insert(int $datasetId, array $data): int
    {
        $connection = $this->entityManager->getConnection();
        $tableName = $this->getTableName($datasetId);
        $connection->insert($connection->quoteSingleIdentifier($tableName), $data);

        return (int) $connection->lastInsertId();
    }

    /**
     * @param array<string, mixed> $data
     * @throws \Doctrine\DBAL\Exception
     */
    public function update(int $datasetId, int $id, array $data): void
    {
        $connection = $this->entityManager->getConnection();
        $tableName = $this->getTableName($datasetId);

        $connection->update($tableName, $data, ['id' => $id]);
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function delete(int $datasetId, int $id): void
    {
        $connection = $this->entityManager->getConnection();
        $tableName = $this->getTableName($datasetId);

        $connection->delete($tableName, ['id' => $id]);
    }

    /**
     * @param DatasetColumn[] $columns
     * @throws \Doctrine\DBAL\Exception
     */
    public function createTable(int $datasetId, array $columns): void
    {
        $connection = $this->entityManager->getConnection();
        $schemaManager = $connection->createSchemaManager();
        $tableName = $this->getTableName($datasetId);

        if ($schemaManager->tablesExist([$tableName])) {
            return;
        }

        $table = new Table($tableName);
        $table->addColumn('id', Types::INTEGER, ['autoincrement' => true, 'unsigned' => true]);
        $table->setPrimaryKey(['id']);

        foreach ($columns as $column) {
            $columnName = self::DATA_COLUMN_PREFIX . $column->getColumnId();
            $table->addColumn($columnName, $column->getType()->getDoctrineType(), [
                'notnull' => $column->isRequired(),
            ]);
        }

        $schemaManager->createTable($table);
    }

    /**
     * @param DatasetColumn[] $columns
     * @throws \Doctrine\DBAL\Exception
     * @throws TypesException
     */
    public function updateTable(int $datasetId, array $columns): void
    {
        $connection = $this->entityManager->getConnection();
        $schemaManager = $connection->createSchemaManager();
        $tableName = $this->getTableName($datasetId);

        if (!$schemaManager->tablesExist([$tableName])) {
            $this->createTable($datasetId, $columns);
            return;
        }

        Assert::stringNotEmpty($tableName, 'Table name must not be empty.');
        $existingTable = $schemaManager->introspectTableByUnquotedName($tableName);
        $newTable = clone $existingTable;

        foreach ($columns as $column) {
            $columnName = self::DATA_COLUMN_PREFIX . $column->getColumnId();
            $options = ['notnull' => $column->isRequired()];

            if (!$newTable->hasColumn($columnName)) {
                $newTable->addColumn(
                    $columnName,
                    $column->getType()->getDoctrineType(),
                    $options
                );
            } else {
                $existingColumn = $newTable->getColumn($columnName);
                $existingColumn->setType(\Doctrine\DBAL\Types\Type::getType($column->getType()->getDoctrineType()));
                $existingColumn->setNotnull($column->isRequired());
            }
        }

        $comparator = $schemaManager->createComparator();
        $diff = $comparator->compareTables($existingTable, $newTable);

        if (!$diff->isEmpty()) {
            $schemaManager->alterTable($diff);
        }
    }
}
