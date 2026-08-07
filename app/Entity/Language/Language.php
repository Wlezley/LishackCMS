<?php

declare(strict_types=1);

namespace App\Entity\Language;

use App\Entity\BaseEntity;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'language')]
class Language extends BaseEntity
{
    #[ORM\Column(type: Types::TEXT, length: 2)]
    private string $lang; // TODO: Change to $languageCode;

    #[ORM\Column(type: Types::TEXT, length: 50)]
    private string $name;

    #[ORM\Column(type: Types::TEXT, length: 2)]
    private string $htmlLang;

    #[ORM\Column(type: Types::TEXT, length: 5)]
    private string $locale;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => true])]
    private bool $enabled = true;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private bool $default = false;

    public function __construct(
        string $lang,
        string $name,
        string $htmlLang,
        string $locale,
        bool $enabled = true,
        bool $default = false,
    ) {
        $this->lang = $lang;
        $this->name = $name;
        $this->htmlLang = $htmlLang;
        $this->locale = $locale;
        $this->enabled = $enabled;
        $this->default = $default;
    }

    public function getLang(): string
    {
        return $this->lang;
    }

    public function setLang(string $lang): void
    {
        $this->lang = $lang;
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
