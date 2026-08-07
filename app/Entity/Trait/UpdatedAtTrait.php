<?php

declare(strict_types=1);

namespace App\Entity\Trait;

use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Adds an automatically managed entity update timestamp.
 *
 * Requires #[ORM\HasLifecycleCallbacks] on the entity.
 */
trait UpdatedAtTrait
{
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private DateTimeImmutable $updatedAt;

    /**
     * Returns the date and time when the entity was last updated.
     */
    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * Sets the date and time when the entity was last updated.
     */
    public function setUpdatedAt(DateTimeImmutable $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    protected function touchUpdatedAt(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }
}
