<?php

declare(strict_types=1);

namespace App\Entity\Language;

use App\Entity\BaseRepository;
use App\Enum\ErrorCode\TranslatorErrorCode;
use App\Exception\TranslatorException;

/**
 * @extends BaseRepository<Language>
 */
final readonly class LanguageRepository extends BaseRepository implements LanguageRepositoryInterface
{
    /**
     * @throws TranslatorException
     */
    public function findByLanguageCode(string $languageCode): Language
    {
        $language = $this->findOneBy([
            'languageCode' => $languageCode,
        ]);

        if ($language === null) {
            throw new TranslatorException(
                "Language '$languageCode' not found.",
                TranslatorErrorCode::LanguageNotFound,
            );
        }

        return $language;
    }

    /**
     * @inheritDoc
     */
    public function findEnabled(): array
    {
        return $this->findBy(
            ['enabled' => true],
            ['id' => 'ASC'],
        );
    }

    /**
     * @throws TranslatorException
     */
    public function findDefault(): Language
    {
        $language = $this->findOneBy([
            'default' => true,
        ]);

        if ($language === null) {
            throw new TranslatorException(
                'Default language not defined.',
                TranslatorErrorCode::DefaultLanguageNotFound,
            );
        }

        return $language;
    }
}
