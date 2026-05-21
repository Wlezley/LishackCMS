<?php

declare(strict_types=1);

namespace App\Dto\Localization;

use Nette\Database\Table\ActiveRow;

class LanguageDto
{
    public function __construct(
        public ?int $id,
        public string $lang,
        public string $name,
        public string $htmlLang,
        public string $locale,
        public bool $enabled = true,
        public bool $default = false,
    ) {
    }

    public static function fromEntity(ActiveRow $row): self
    {
        return new self(
            id: $row->id,
            lang: $row->lang,
            name: $row->name,
            htmlLang: $row->html_lang, // @phpcs:ignore Squiz.NamingConventions.ValidVariableName
            locale: $row->locale,
            enabled: $row->enabled == 1,
            default: $row->default == 1,
        );
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'lang' => $this->lang,
            'name' => $this->name,
            'html_lang' => $this->htmlLang,
            'locale' => $this->locale,
            'enabled' => $this->enabled,
            'default' => $this->default,
        ];
    }
}
