<?php

declare(strict_types=1);

namespace App\Entity\TranslationLog;

use App\Entity\BaseEntity;
use App\Enum\TranslationLogType;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'translations_log')]
class TranslationLog extends BaseEntity
{
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['default' => 'CURRENT_TIMESTAMP'])]
    private \DateTimeImmutable $date; // TODO: Change to $createdAt !!! (trait ???)

    /**
     * @todo KEY + LANG must be unique
     */
    #[ORM\Column(type: Types::TEXT, length: 255)]
    private string $key;

    #[ORM\Column(type: Types::TEXT, length: 2)]
    private string $lang; // TODO: Change to $languageCode;

    #[ORM\Column(enumType: TranslationLogType::class, options: ['default' => TranslationLogType::Unk])]
    private TranslationLogType $type;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $message;

    public function __construct(
        string $key,
        string $lang,
        TranslationLogType $type = TranslationLogType::Unk,
        ?string $message = null,
    ) {
        $this->key = $key;
        $this->lang = $lang;
        $this->type = $type;
        $this->message = $message;
    }

    public function getDate(): \DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(\DateTimeImmutable $date): void
    {
        $this->date = $date;
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

    public function getType(): TranslationLogType
    {
        return $this->type;
    }

    public function setType(TranslationLogType $type): void
    {
        $this->type = $type;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): void
    {
        $this->message = $message;
    }
}
