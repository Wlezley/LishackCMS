<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\MappedSuperclass]
abstract class BaseEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    protected int $id;

    public function __clone()
    {
        if (isset($this->id)) {
            unset($this->id);
        }
    }

    /**
     * Returns the entity identifier.
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Determines whether the entity has already been persisted.
     */
    public function isPersisted(): bool
    {
        return isset($this->id);
    }
}
