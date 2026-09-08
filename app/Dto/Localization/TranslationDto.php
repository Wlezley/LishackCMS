<?php

declare(strict_types=1);

namespace App\Dto\Localization;

use App\Entity\Language\Language;
use App\Entity\Translation\Translation;

class TranslationDto
{
    public function __construct(
        public int $id,
        public string $key,
        public Language $language,
        public ?string $text = null,
    ) {
    }

    public static function fromEntity(Translation $translation): self
    {
        return new self(
            id: $translation->getId(),
            key: $translation->getKey(),
            language: $translation->getLanguage(),
            text: $translation->getText(),
        );
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'key' => $this->key,
            'language' => LanguageDto::fromEntity($this->language)->toArray(),
            'text' => $this->text,
        ];
    }
}
