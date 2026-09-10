<?php

declare(strict_types=1);

namespace App\Entity\Article;

use App\Entity\BaseRepository;

/**
 * @extends BaseRepository<Article>
 */
final readonly class ArticleRepository extends BaseRepository implements ArticleRepositoryInterface
{
    /**
     * @return list<Article>
     */
    public function findBySearch(?string $search = null, ?int $categoryId = null, ?int $limit = null, ?int $offset = null): array
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('a')
            ->from(Article::class, 'a')
            ->orderBy('a.id', 'ASC');

        if ($search !== null) {
            $qb->andWhere('a.title LIKE :search OR a.content LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        if ($categoryId !== null) {
            $qb->andWhere('a.categoryId = :categoryId')
                ->setParameter('categoryId', $categoryId);
        }

        if ($limit !== null) {
            $qb->setMaxResults($limit);
        }

        if ($offset !== null) {
            $qb->setFirstResult($offset);
        }

        return $qb->getQuery()->getResult();
    }

    public function countBySearch(?string $search = null, ?int $categoryId = null): int
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('COUNT(a.id)')
            ->from(Article::class, 'a');

        if ($search !== null) {
            $qb->andWhere('a.title LIKE :search OR a.content LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        if ($categoryId !== null) {
            $qb->andWhere('a.categoryId = :categoryId')
                ->setParameter('categoryId', $categoryId);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}
