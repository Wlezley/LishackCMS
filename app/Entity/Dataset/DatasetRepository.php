<?php

declare(strict_types=1);

namespace App\Entity\Dataset;

use App\Entity\BaseRepository;

/**
 * @extends BaseRepository<Dataset>
 */
final readonly class DatasetRepository extends BaseRepository implements DatasetRepositoryInterface
{
    /**
     * @return list<Dataset>
     */
    public function findBySearch(?string $search = null, ?int $limit = null, ?int $offset = null, bool $includeDeleted = false): array
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('d')
            ->from(Dataset::class, 'd')
            ->orderBy('d.id', 'ASC');

        if (!$includeDeleted) {
            $qb->andWhere('d.deleted = :deleted')
                ->setParameter('deleted', false);
        }

        if ($search !== null) {
            $qb->andWhere('d.name LIKE :search OR d.slug LIKE :search OR d.component LIKE :search OR d.presenter LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        if ($limit !== null) {
            $qb->setMaxResults($limit);
        }

        if ($offset !== null) {
            $qb->setFirstResult($offset);
        }

        return $qb->getQuery()->getResult();
    }

    public function countBySearch(?string $search = null, bool $includeDeleted = false): int
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('COUNT(d.id)')
            ->from(Dataset::class, 'd');

        if (!$includeDeleted) {
            $qb->andWhere('d.deleted = :deleted')
                ->setParameter('deleted', false);
        }

        if ($search !== null) {
            $qb->andWhere('d.name LIKE :search OR d.slug LIKE :search OR d.component LIKE :search OR d.presenter LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}
