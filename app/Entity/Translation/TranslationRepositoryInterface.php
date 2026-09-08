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
    public function findBySearch(string $languageCode, ?string $search = null, ?int $limit = null, ?int $offset = null): array;

    public function countBySearch(string $languageCode, ?string $search = null): int;

    /**
     * @return list<Translation>
     */
    public function findByKey(string $key): array;

    public function findOneByKeyAndLanguage(string $key, Language $language): ?Translation;

    public function existsByKeyAndLanguage(string $key, Language $language): bool;
}
