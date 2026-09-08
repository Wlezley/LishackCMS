<?php

declare(strict_types=1);

namespace App\Entity\TranslationLog;

use App\Entity\BaseEntity;
use App\Entity\Trait\CreatedAtTrait;
use App\Entity\Trait\HasIdTrait;
use App\Enum\TranslationLogType;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'translations_log')]
class TranslationLog extends BaseEntity
{
    use HasIdTrait;
    use CreatedAtTrait;

    /**
     * @todo KEY + LANG must be unique
     */
    public function __construct(
        #[ORM\Column(type: Types::STRING, length: 255)]
        private string $key,
        #[ORM\Column(type: Types::STRING, length: 2)]
        private string $lang, // TODO: Change to $languageCode; OR use entity Language???
        #[ORM\Column(enumType: TranslationLogType::class, options: ['default' => TranslationLogType::Unk])]
        private TranslationLogType $type = TranslationLogType::Unk,
        #[ORM\Column(type: Types::TEXT, nullable: true, options: ['default' => null])]
        private ?string $message = null,
    ) {
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
