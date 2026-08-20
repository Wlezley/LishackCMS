<?php

declare(strict_types=1);

namespace App\Entity\Translation;

use App\Entity\BaseEntity;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'translations')]
class Translation extends BaseEntity
{
    /**
     * @todo KEY + LANG must be unique
     */
    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $key;

    #[ORM\Column(type: Types::STRING, length: 2)]
    private string $lang; // TODO: Change to $languageCode; OR use entity Language???

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $text = null;

    public function __construct(
        string $key,
        string $lang,
        ?string $text = null,
    ) {
        $this->key = $key;
        $this->lang = $lang;
        $this->text = $text;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function setKey(string $key): void
    {
        $this->key = $key;
    }

    public function getLang(): string
    {
        return $this->lang;
    }

    public function setLang(string $lang): void
    {
        $this->lang = $lang;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(?string $text): void
    {
        $this->text = $text;
    }
}
