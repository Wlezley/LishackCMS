<?php

declare(strict_types=1);

namespace App\Entity\DatasetColumn;

use App\Entity\BaseRepository;

/**
 * @extends BaseRepository<DatasetColumn>
 */
final readonly class DatasetColumnRepository extends BaseRepository implements DatasetColumnRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function findByDatasetId(int $datasetId, bool $deletedOnly = false): array
    {
        $qb = $this->entityManager->createQueryBuilder();

        $qb->select('dc')
            ->from(DatasetColumn::class, 'dc')
            ->innerJoin('dc.dataset', 'd')
            ->where('d.id = :datasetId')
            ->setParameter('datasetId', $datasetId);

        // TODO: This filter system needs to be reworked... WTF ????????
        if (!$deletedOnly) {
            $qb->andWhere('dc.deleted = :deleted')
                ->setParameter('deleted', false);
        }

        return $qb->getQuery()->getResult();
    }
}
