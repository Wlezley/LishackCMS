<?php

declare(strict_types=1);

namespace App\Dto\Localization;

use App\Entity\Language\Language;

class LanguageDto
{
    public function __construct(
        public ?int $id,
        public string $languageCode,
        public string $name,
        public string $htmlLang,
        public string $locale,
        public bool $enabled = true,
        public bool $default = false,
    ) {
    }

    public static function fromEntity(Language $language): self
    {
        return new self(
            id: $language->getId(),
            languageCode: $language->getLanguageCode(),
            name: $language->getName(),
            htmlLang: $language->getHtmlLang(),
            locale: $language->getLocale(),
            enabled: $language->isEnabled(),
            default: $language->isDefault(),
        );
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'languageCode' => $this->languageCode,
            'name' => $this->name,
            'htmlLang' => $this->htmlLang,
            'locale' => $this->locale,
            'enabled' => $this->enabled,
            'default' => $this->default,
        ];
    }
}
