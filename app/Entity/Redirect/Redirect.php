<?php

declare(strict_types=1);

namespace App\Entity\Redirect;

use App\Entity\BaseEntity;
use App\Enum\HttpRedirectCode;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RedirectRepository::class)]
#[ORM\Table(name: 'redirect')]
class Redirect extends BaseEntity
{
    #[ORM\Column(type: Types::STRING, length: 300)]
    private string $source;

    #[ORM\Column(type: Types::STRING, length: 300)]
    private string $target;

    #[ORM\Column(enumType: HttpRedirectCode::class, options: ['default' => HttpRedirectCode::FOUND])]
    private HttpRedirectCode $code;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => true])]
    private bool $enabled;

    public function __construct(
        string $source,
        string $target,
        HttpRedirectCode $code = HttpRedirectCode::FOUND,
        bool $enabled = true,
    ) {
        $this->source = $source;
        $this->target = $target;
        $this->code = $code;
        $this->enabled = $enabled;
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
