<?php

declare(strict_types=1);

namespace App\Entity\Language;

use App\Entity\BaseEntity;
use App\Entity\Trait\HasIdTrait;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'language')]
class Language extends BaseEntity
{
    use HasIdTrait;

    public function __construct(
        #[ORM\Column(type: Types::STRING, length: 2, unique: true)]
        private string $languageCode,
        #[ORM\Column(type: Types::STRING, length: 50)]
        private string $name,
        #[ORM\Column(type: Types::STRING, length: 2)]
        private string $htmlLang,
        #[ORM\Column(type: Types::STRING, length: 5)]
        private string $locale,
        #[ORM\Column(type: Types::BOOLEAN, options: ['default' => true])]
        private bool $enabled = true,
        #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
        private bool $default = false,
    ) {
    }

    public function getLanguageCode(): string
    {
        return $this->languageCode;
    }

    public function setLanguageCode(string $languageCode): void
    {
        $this->languageCode = $languageCode;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getHtmlLang(): string
    {
        return $this->htmlLang;
    }

    public function setHtmlLang(string $htmlLang): void
    {
        $this->htmlLang = $htmlLang;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): void
    {
        $this->locale = $locale;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    public function isDefault(): bool
    {
        return $this->default;
    }

    public function setDefault(bool $default): void
    {
        $this->default = $default;
    }
}
