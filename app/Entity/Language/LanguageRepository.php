<?php

declare(strict_types=1);

namespace App\Entity\Language;

use App\Entity\BaseRepository;

/**
 * @extends BaseRepository<Language>
 */
final readonly class LanguageRepository extends BaseRepository implements LanguageRepositoryInterface
{
    public function findByLanguageCode(string $languageCode): ?Language
    {
        return $this->findOneBy([
            'languageCode' => $languageCode,
        ]);
    }

    /**
     * @return list<Language>
     */
    public function findEnabled(): array
    {
        return $this->findBy(
            ['enabled' => true],
            ['id' => 'ASC'],
        );
    }

    public function findDefault(): ?Language
    {
        return $this->findOneBy([
            'default' => true,
        ]);
    }
}
