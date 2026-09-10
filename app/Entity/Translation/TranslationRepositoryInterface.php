<?php

declare(strict_types=1);

namespace App\Entity\Translation;

use App\Entity\BaseRepositoryInterface;
use App\Entity\Language\Language;

/**
 * @extends BaseRepositoryInterface<Translation>
 */
interface TranslationRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * @return list<Translation>
     */
    public function findByLanguage(Language $language): array;

    /**
     * @return list<Translation>
     */
    public function findByLanguageCode(string $languageCode): array;

    /**
     * @return list<Translation>
     */
    public function findByLanguages(string $targetLanguage, string $defaultLanguage): array;

    /**
     * @return list<Translation>
     */
    public function findBySearch(Language $language, ?string $search = null, ?int $limit = null, ?int $offset = null): array;

    public function countBySearch(Language $language, ?string $search = null): int;

    /**
     * @return list<Translation>
     */
    public function findByKey(string $translationKey): array;

    public function findOneByKeyAndLanguage(string $translationKey, Language $language): ?Translation;

    public function existsByKeyAndLanguage(string $translationKey, Language $language): bool;
}
