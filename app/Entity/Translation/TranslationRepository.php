<?php

declare(strict_types=1);

namespace App\Entity\Translation;

use App\Entity\BaseRepository;
use App\Entity\Language\Language;

/**
 * @extends BaseRepository<Translation>
 */
final readonly class TranslationRepository extends BaseRepository implements TranslationRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function findByLanguage(Language $language): array
    {
        return $this->findBy([
            'language' => $language,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function findByLanguageCode(string $languageCode): array
    {
        $qb = $this->entityManager->createQueryBuilder();

        $qb->select('t')
            ->from(Translation::class, 't')
            ->join('t.language', 'l')
            ->where('l.code = :languageCode')
            ->setParameter('languageCode', $languageCode);

        return $qb->getQuery()->getResult();
    }

    /**
     * @inheritDoc
     */
    public function findByLanguages(string $targetLanguage, string $defaultLanguage): array
    {
        $qb = $this->entityManager->createQueryBuilder();

        $qb->select('t')
            ->from(Translation::class, 't')
            ->join('t.language', 'l')
            ->where('l.code IN (:languages)')
            ->setParameter('languages', [
                $targetLanguage,
                $defaultLanguage,
            ]);

        return $qb->getQuery()->getResult();
    }

    /**
     * @inheritDoc
     */
    public function findBySearch(
        string $languageCode,
        ?string $search = null,
        ?int $limit = null,
        ?int $offset = null,
    ): array {
        $qb = $this->entityManager->createQueryBuilder();

        $qb->select('t')
            ->from(Translation::class, 't')
            ->join('t.language', 'l')
            ->where('l.code = :languageCode')
            ->setParameter('languageCode', $languageCode)
            ->orderBy('t.key', 'ASC');

        if ($search !== null) {
            $qb->andWhere(
                't.key LIKE :search OR t.text LIKE :search'
            )->setParameter(
                'search',
                '%' . $search . '%'
            );
        }

        $qb->setMaxResults($limit);
        $qb->setFirstResult($offset);

        return $qb->getQuery()->getResult();
    }

    public function countBySearch(string $languageCode, ?string $search = null): int
    {
        $qb = $this->entityManager->createQueryBuilder();

        $qb->select('COUNT(t.id)')
            ->from(Translation::class, 't')
            ->join('t.language', 'l')
            ->where('l.code = :languageCode')
            ->setParameter('languageCode', $languageCode);

        if ($search !== null) {
            $qb->andWhere(
                't.key LIKE :search OR t.text LIKE :search'
            )->setParameter(
                'search',
                '%' . $search . '%'
            );
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @inheritDoc
     */
    public function findByKey(string $key): array
    {
        return $this->findBy([
            'key' => $key,
        ]);
    }

    public function findOneByKeyAndLanguage(string $key, Language $language): ?Translation
    {
        return $this->findOneBy([
            'key' => $key,
            'language' => $language,
        ]);
    }

    public function existsByKeyAndLanguage(string $key, Language $language): bool
    {
        return $this->findOneByKeyAndLanguage($key, $language) !== null;
    }
}
