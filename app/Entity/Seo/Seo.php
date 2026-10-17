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
        #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
        private ?string $metaKeywords = null,
    ) {
    }

    public function getRobots(): ?string
    {
        return $this->robots;
    }

    public function setRobots(?string $robots): self
    {
        $this->robots = $robots;
        return $this;
    }

    public function getCanonicalUrl(): ?string
    {
        return $this->canonicalUrl;
    }

    public function setCanonicalUrl(?string $canonicalUrl): self
    {
        $this->canonicalUrl = $canonicalUrl;
        return $this;
    }

    public function getOgTitle(): ?string
    {
        return $this->ogTitle;
    }

    public function setOgTitle(?string $ogTitle): self
    {
        $this->ogTitle = $ogTitle;
        return $this;
    }

    public function getOgDescription(): ?string
    {
        return $this->ogDescription;
    }

    public function setOgDescription(?string $ogDescription): self
    {
        $this->ogDescription = $ogDescription;
        return $this;
    }

    public function getOgImage(): ?string
    {
        return $this->ogImage;
    }

    public function setOgImage(?string $ogImage): self
    {
        $this->ogImage = $ogImage;
        return $this;
    }

    public function getOgUrl(): ?string
    {
        return $this->ogUrl;
    }

    public function setOgUrl(?string $ogUrl): self
    {
        $this->ogUrl = $ogUrl;
        return $this;
    }

    public function getOgType(): ?string
    {
        return $this->ogType;
    }

    public function setOgType(?string $ogType): self
    {
        $this->ogType = $ogType;
        return $this;
    }

    public function getMetaTitle(): ?string
    {
        return $this->metaTitle;
    }

    public function setMetaTitle(?string $metaTitle): self
    {
        $this->metaTitle = $metaTitle;
        return $this;
    }

    public function getMetaDescription(): ?string
    {
        return $this->metaDescription;
    }

    public function setMetaDescription(?string $metaDescription): self
    {
        $this->metaDescription = $metaDescription;
        return $this;
    }

    public function getMetaKeywords(): ?string
    {
        return $this->metaKeywords;
    }

    public function setMetaKeywords(?string $metaKeywords): self
    {
        $this->metaKeywords = $metaKeywords;
        return $this;
    }
}
