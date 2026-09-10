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
            ->join('t.language', 'l') // TODO: Use Language entity
            ->where('l.code = :language')
            ->setParameter('language', $languageCode);

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
            ->join('t.language', 'l') // TODO: Use Language entity
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
        Language $language,
        ?string $search = null,
        ?int $limit = null,
        ?int $offset = null,
    ): array {
        $qb = $this->entityManager->createQueryBuilder();

        $qb->select('t')
            ->from(Translation::class, 't')
            ->where('t.language = :language')
            ->setParameter('language', $language)
            ->orderBy('t.translationKey', 'ASC');

        if ($search !== null) {
            $qb->andWhere(
                't.translationKey LIKE :search OR t.text LIKE :search'
            )->setParameter(
                'search',
                '%' . $search . '%'
            );
        }

        $qb->setMaxResults($limit);
        $qb->setFirstResult($offset);

        return $qb->getQuery()->getResult();
    }

    public function countBySearch(Language $language, ?string $search = null): int
    {
        $qb = $this->entityManager->createQueryBuilder();

        $qb->select('COUNT(t.id)')
            ->from(Translation::class, 't')
            ->where('t.language = :language')
            ->setParameter('language', $language);

        if ($search !== null) {
            $qb->andWhere(
                't.translationKey LIKE :search OR t.text LIKE :search'
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
    public function findByKey(string $translationKey): array
    {
        return $this->findBy([
            'translationKey' => $translationKey,
        ]);
    }

    public function findOneByKeyAndLanguage(string $translationKey, Language $language): ?Translation
    {
        return $this->findOneBy([
            'translationKey' => $translationKey,
            'language' => $language,
        ]);
    }

    public function existsByKeyAndLanguage(string $translationKey, Language $language): bool
    {
        return $this->findOneByKeyAndLanguage($translationKey, $language) !== null;
    }
}
