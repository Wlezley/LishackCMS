<?php

declare(strict_types=1);

namespace App\Entity\DatasetColumn;

use App\Entity\BaseRepository;

/**
 * @extends BaseRepository<DatasetColumn>
 */
final readonly class DatasetColumnRepository extends BaseRepository
{
    public function getMaxColumnId(int $datasetId): int
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('MAX(dc.columnId)')
            ->from(DatasetColumn::class, 'dc')
            ->where('dc.datasetId = :datasetId')
            ->setParameter('datasetId', $datasetId);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}
