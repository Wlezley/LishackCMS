<?php

declare(strict_types=1);

namespace App\Dto\User;

use App\Entity\User\User;
use App\Enum\UserRoleEnum;

class UserDTO
{
    public function __construct(
        public int $id,
        public string $userName,
        public string $email,
        // #[\SensitiveParameter]
        // public string $password,
        public UserRoleEnum $role = UserRoleEnum::Guest,
        public ?string $firstName = null,
        public ?string $lastName = null,
        public ?string $sessionId = null,
        public bool $deleted = false,
        public bool $enabled = true,
        public ?\DateTimeImmutable $lastLoginAt = null,
    ) {
    }

    public static function fromEntity(User $user): self
    {
        return new self(
            id: $user->getId(),
            userName: $user->getUserName(),
            email: $user->getEmail(),
            role: $user->getRole(),
            firstName: $user->getFirstName(),
            lastName: $user->getLastName(),
            sessionId: $user->getSessionId(),
            deleted: $user->isDeleted(),
            enabled: $user->isEnabled(),
            lastLoginAt: $user->getLastLoginAt(),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'userName' => $this->userName,
            'email' => $this->email,
            'role' => $this->role,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'sessionId' => $this->sessionId,
            'deleted' => $this->deleted,
            'enabled' => $this->enabled,
            'lastLoginAt' => $this->lastLoginAt,
        ];
    }
}
