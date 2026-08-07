<?php

declare(strict_types=1);

namespace App\Entity\Redirect;

use App\Entity\BaseRepository;

/**
 * @extends BaseRepository<Redirect>
 */
final readonly class RedirectRepository extends BaseRepository implements RedirectRepositoryInterface
{
    /**
     * @return class-string<Redirect>
     */
    protected static function getEntityClass(): string
    {
        return Redirect::class;
    }

    /** @inheritDoc */
    public function findBySource(string $source): ?Redirect
    {
        return $this->findOneBy(
            criteria: ['source' => $source]
        );
    }

    /** @inheritDoc */
    public function findEnabledBySource(string $source): ?Redirect
    {
        return $this->findOneBy(
            criteria: [
                'enabled' => true,
                'source' => $source,
            ]
        );
    }

    /** @inheritDoc */
    public function findAllEnabled(): array
    {
        return $this->findBy(
            criteria: ['enabled' => true],
            orderBy: ['id' => 'ASC'],
        );
    }

    /** @inheritDoc */
    public function existsBySource(string $source): bool
    {
        return $this->exists(
            criteria: ['source' => $source]
        );
    }

    /** @inheritDoc */
    public function countBySearch(?string $search = null): int
    {
        $qb = $this->entityManager->createQueryBuilder();

        $qb->select('COUNT(r.id)')
            ->from(Redirect::class, 'r');

        if ($search !== null && $search !== '') {
            $qb->andWhere('r.source LIKE :search OR r.target LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /** @inheritDoc */
    public function findPaginated(
        int $limit,
        int $offset,
        ?string $search = null,
    ): array {
        $qb = $this->entityManager->createQueryBuilder();

        $qb->select('r')
            ->from(Redirect::class, 'r')
            ->orderBy('r.id', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        if ($search !== null && $search !== '') {
            $qb->andWhere('r.source LIKE :search OR r.target LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        /** @var list<Redirect> */
        return $qb->getQuery()->getResult();
    }
}
