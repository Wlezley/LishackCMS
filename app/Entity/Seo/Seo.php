<?php

declare(strict_types=1);

namespace App\Entity\Seo;

use App\Entity\BaseEntity;
use App\Entity\Trait\HasIdTrait;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'seo')]
class Seo extends BaseEntity
{
    use HasIdTrait;

    public function __construct(
        #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
        private ?string $robots = null,
        #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
        private ?string $canonicalUrl = null,
        #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
        private ?string $ogTitle = null,
        #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
        private ?string $ogDescription = null,
        #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
        private ?string $ogImage = null,
        #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
        private ?string $ogUrl = null,
        #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
        private ?string $ogType = null,
        #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
        private ?string $metaTitle = null,
        #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
        private ?string $metaDescription = null,
    ) {
    }

    public function getRobots(): ?string
    {
        return $this->robots;
    }

    public function setRobots(?string $robots): void
    {
        $this->robots = $robots;
    }

    public function getCanonicalUrl(): ?string
    {
        return $this->canonicalUrl;
    }

    public function setCanonicalUrl(?string $canonicalUrl): void
    {
        $this->canonicalUrl = $canonicalUrl;
    }

    public function getOgTitle(): ?string
    {
        return $this->ogTitle;
    }

    public function setOgTitle(?string $ogTitle): void
    {
        $this->ogTitle = $ogTitle;
    }

    public function getOgDescription(): ?string
    {
        return $this->ogDescription;
    }

    public function setOgDescription(?string $ogDescription): void
    {
        $this->ogDescription = $ogDescription;
    }

    public function getOgImage(): ?string
    {
        return $this->ogImage;
    }

    public function setOgImage(?string $ogImage): void
    {
        $this->ogImage = $ogImage;
    }

    public function getOgUrl(): ?string
    {
        return $this->ogUrl;
    }

    public function setOgUrl(?string $ogUrl): void
    {
        $this->ogUrl = $ogUrl;
    }

    public function getOgType(): ?string
    {
        return $this->ogType;
    }

    public function setOgType(?string $ogType): void
    {
        $this->ogType = $ogType;
    }

    public function getMetaTitle(): ?string
    {
        return $this->metaTitle;
    }

    public function setMetaTitle(?string $metaTitle): void
    {
        $this->metaTitle = $metaTitle;
    }

    public function getMetaDescription(): ?string
    {
        return $this->metaDescription;
    }

    public function setMetaDescription(?string $metaDescription): void
    {
        $this->metaDescription = $metaDescription;
    }
}
