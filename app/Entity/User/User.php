<?php

declare(strict_types=1);

namespace App\Entity\User;

use App\Entity\BaseEntity;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'users')]
//#[ORM\HasLifecycleCallbacks]
class User extends BaseEntity
{
//    use CreatedAtTrait;
//    use UpdatedAtTrait;

    #[ORM\Column(type: Types::TEXT, length: 50, unique: true)]
    private string $name; // TODO: Change to $username

    #[ORM\Column(type: Types::TEXT, length: 255)]
    private string $password;

    #[ORM\Column(type: Types::TEXT, length: 255)]
    private string $email;

    #[ORM\Column(type: Types::TEXT, length: 50)]
    private string $role; // TODO: Change to enum

    #[ORM\Column(type: Types::TEXT, length: 255)]
    private string $fullName; // TODO: Split to $firstName $lastName

    #[ORM\Column(type: Types::TEXT, length: 150)] // TODO: Allow more than 150 chars ???
    private string $sessionId;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private bool $deleted = false;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => true])]
    private bool $enabled = true;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, options: ['default' => 'CURRENT_TIMESTAMP'])]
    private \DateTimeImmutable $created; // TODO: Change to $createdAt !!!

    // TODO: Add $updatedAt !!! (trait ???)

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $lastLogin = null; // TODO: Change to $lastLoginAt !!!

    public function __construct(
        string $name,
        string $password,
        string $email,
        string $role,
        string $fullName,
        string $sessionId,
        bool $deleted = false,
        bool $enabled = true,
        ?\DateTimeImmutable $created = null,
        // ?\DateTimeImmutable $updatedAt = null, // TODO...
        ?\DateTimeImmutable $lastLogin = null,
    ) {
        $this->name = $name;
        $this->password = $password;
        $this->email = $email;
        $this->role = $role;
        $this->fullName = $fullName;
        $this->sessionId = $sessionId;
        $this->deleted = $deleted;
        $this->enabled = $enabled;
        $this->created = $created ?? new \DateTimeImmutable();
        $this->lastLogin = $lastLogin;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function setRole(string $role): void
    {
        $this->role = $role;
    }

    public function getFullName(): string
    {
        return $this->fullName;
    }

    public function setFullName(string $fullName): void
    {
        $this->fullName = $fullName;
    }

    public function getSessionId(): string
    {
        return $this->sessionId;
    }

    public function setSessionId(string $sessionId): void
    {
        $this->sessionId = $sessionId;
    }

    public function isDeleted(): bool
    {
        return $this->deleted;
    }

    public function setDeleted(bool $deleted): void
    {
        $this->deleted = $deleted;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    public function getCreated(): \DateTimeImmutable
    {
        return $this->created;
    }

    public function setCreated(\DateTimeImmutable $created): void
    {
        $this->created = $created;
    }

    public function getLastLogin(): ?\DateTimeImmutable
    {
        return $this->lastLogin;
    }

    public function setLastLogin(?\DateTimeImmutable $lastLogin): void
    {
        $this->lastLogin = $lastLogin;
    }
}
