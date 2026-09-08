<?php

declare(strict_types=1);

namespace App\Entity\Trait;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

trait HasIdTrait
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    protected ?int $id = null;

    public function __clone(): void
    {
        $this->id = null;
    }

    /**
     * Returns the entity identifier.
     *
     * @throws \LogicException If the entity has not been persisted yet.
     */
    public function getId(): int
    {
        if ($this->id === null) {
            throw new \LogicException('Entity has not been persisted yet.');
        }

        return $this->id;
    }

    /**
     * Determines whether the entity has already been persisted.
     */
    public function isPersisted(): bool
    {
        return $this->id !== null;
    }
}
