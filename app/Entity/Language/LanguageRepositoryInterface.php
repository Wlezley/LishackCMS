<?php

declare(strict_types=1);

namespace App\Entity\Language;

use App\Entity\BaseRepositoryInterface;

/**
 * @extends BaseRepositoryInterface<Language>
 */
interface LanguageRepositoryInterface extends BaseRepositoryInterface
{
    public function findByLanguageCode(string $languageCode): Language;

    /**
     * @return list<Language>
     */
    public function findEnabled(): array;

    public function findDefault(): Language;
}
