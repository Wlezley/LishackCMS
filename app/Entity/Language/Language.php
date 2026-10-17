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
    public function __construct(
        #[ORM\Id]
        #[ORM\Column(type: Types::STRING, length: 2)]
        private string $code,
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

    public function getCode(): string
    {
        return $this->code;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getHtmlLang(): string
    {
        return $this->htmlLang;
    }

    public function setHtmlLang(string $htmlLang): self
    {
        $this->htmlLang = $htmlLang;
        return $this;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): self
    {
        $this->locale = $locale;
        return $this;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;
        return $this;
    }

    public function isDefault(): bool
    {
        return $this->default;
    }

    public function setDefault(bool $default): self
    {
        $this->default = $default;
        return $this;
    }
}
