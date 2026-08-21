<?php

declare(strict_types=1);

namespace App\Entity\Trait;

use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Adds an automatically managed entity creation timestamp.
 *
 * Requires #[ORM\HasLifecycleCallbacks] on the entity.
 */
trait CreatedAtTrait
{
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: false, options: ['default' => 'CURRENT_TIMESTAMP'])]
    private DateTimeImmutable $createdAt;

    /**
     * Returns the date and time when the entity was created.
     */
    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * Sets the date and time when the entity was created.
     */
    public function setCreatedAt(DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    #[ORM\PrePersist]
    public function initializeCreatedAt(): void
    {
        $this->createdAt = new DateTimeImmutable();
    }
}
