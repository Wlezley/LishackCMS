<?php

declare(strict_types=1);

namespace App\Entity\Redirect;

use App\Entity\BaseEntity;
use App\Entity\Trait\HasIdTrait;
use App\Enum\HttpRedirectCode;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'redirect')]
class Redirect extends BaseEntity
{
    use HasIdTrait;

    public function __construct(
        #[ORM\Column(type: Types::STRING, length: 300)]
        private string $source,
        #[ORM\Column(type: Types::STRING, length: 300)]
        private string $target,
        #[ORM\Column(enumType: HttpRedirectCode::class, options: ['default' => HttpRedirectCode::FOUND])]
        private HttpRedirectCode $code = HttpRedirectCode::FOUND,
        #[ORM\Column(type: Types::BOOLEAN, options: ['default' => true])]
        private bool $enabled = true,
    ) {
    }

    public function getSource(): string
    {
        return $this->source;
    }

    public function setSource(string $source): void
    {
        $this->source = $source;
    }

    public function getTarget(): string
    {
        return $this->target;
    }

    public function setTarget(string $target): void
    {
        $this->target = $target;
    }

    public function getCode(): HttpRedirectCode
    {
        return $this->code;
    }

    public function setCode(HttpRedirectCode $code): void
    {
        $this->code = $code;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }
}
