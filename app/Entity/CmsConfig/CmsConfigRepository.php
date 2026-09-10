<?php

declare(strict_types=1);

namespace App\Entity\CmsConfig;

use App\Entity\BaseRepository;

/**
 * @extends BaseRepository<CmsConfig>
 */
final readonly class CmsConfigRepository extends BaseRepository implements CmsConfigRepositoryInterface
{
    /**
     * @return list<CmsConfig>
     */
    public function findBySearch(?string $category = null, ?string $search = null, ?int $limit = null, ?int $offset = null): array
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('c')
            ->from(CmsConfig::class, 'c')
            ->orderBy('c.key', 'ASC');

        if ($category !== null) {
            $qb->andWhere('c.category = :category')
                ->setParameter('category', $category);
        }

        if ($search !== null) {
            $qb->andWhere('c.key LIKE :search OR c.value LIKE :search')
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

    public function countBySearch(?string $category = null, ?string $search = null): int
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('COUNT(c.key)')
            ->from(CmsConfig::class, 'c');

        if ($category !== null) {
            $qb->andWhere('c.category = :category')
                ->setParameter('category', $category);
        }

        if ($search !== null) {
            $qb->andWhere('c.key LIKE :search OR c.value LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}
