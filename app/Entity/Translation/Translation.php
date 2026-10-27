<?php

declare(strict_types=1);

namespace App\Entity\Translation;

use App\Entity\BaseEntity;
use App\Entity\Language\Language;
use App\Entity\Trait\HasIdTrait;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(
    name: 'translations',
    uniqueConstraints: [
        new ORM\UniqueConstraint(
            name: 'translation_key_language_unique',
            columns: ['translation_key', 'language'],
        ),
    ],
)]
#[ORM\UniqueConstraint(
    name: 'translation_key_language_unique',
    columns: ['translation_key', 'language'],
)]
class Translation extends BaseEntity
{
    use HasIdTrait;

    public function __construct(
        #[ORM\Column(type: Types::STRING, length: 255)]
        private string $translationKey,
        #[ORM\ManyToOne(targetEntity: Language::class)]
        #[ORM\JoinColumn(
            name: 'language',
            referencedColumnName: 'code',
            nullable: false,
            onDelete: 'RESTRICT',
        )]
        private Language $language,
        #[ORM\Column(type: Types::TEXT, nullable: true, options: ['default' => null])]
        private ?string $text = null,
    ) {
    }

    public function getTranslationKey(): string
    {
        return $this->translationKey;
    }

    public function setTranslationKey(string $translationKey): self
    {
        $this->translationKey = $translationKey;
        return $this;
    }

    public function getLanguage(): Language
    {
        return $this->language;
    }

    public function setLanguage(Language $language): self
    {
        $this->language = $language;
        return $this;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(?string $text): self
    {
        $this->text = $text;
        return $this;
    }

    public function getLanguageCode(): string
    {
        return $this->language->getCode();
    }
}
