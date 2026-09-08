<?php

declare(strict_types=1);

namespace App\Dto\Localization;

use App\Entity\Translation\Translation;

class TranslationCollectionDto
{
    /**
     * @param TranslationDto[] $translations
     */
    public function __construct(
        public array $translations,
    ) {
    }

    /**
     * @param Translation[] $translations
     */
    public static function fromEntities(array $translations): self
    {
        $dto = new self([]);
        foreach ($translations as $translation) {
            $dto->translations[] = TranslationDto::fromEntity($translation);
        }

        return $dto;
    }

    /**
     * @return list<array<string,mixed>>
     */
    public function toArray(): array
    {
        $arr = [];
        foreach ($this->translations as $translation) {
            $arr[] = $translation->toArray();
        }

        return $arr;
    }
}
