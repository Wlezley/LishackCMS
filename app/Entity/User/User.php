<?php

declare(strict_types=1);

namespace App\Entity\User;

use App\Attributes\Sortable;
use App\Entity\BaseEntity;
use App\Entity\Trait\CreatedAtTrait;
use App\Entity\Trait\HasIdTrait;
use App\Entity\Trait\UpdatedAtTrait;
use App\Enum\UserRoleEnum;
use App\Helper\UserPasswordHelper;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'users')]
class User extends BaseEntity
{
    use HasIdTrait;
    use CreatedAtTrait;
    use UpdatedAtTrait;

    public function __construct(
        #[ORM\Column(type: Types::STRING, length: 50, unique: true)]
        #[Sortable]
        private string $userName,
        #[ORM\Column(type: Types::STRING, length: 255)]
        #[Sortable]
        private string $email,
        #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['default' => null])]
        private ?string $password = null,
        #[ORM\Column(
            type: Types::STRING,
            length: UserRoleEnum::MAX_LENGTH,
            enumType: UserRoleEnum::class,
            options: ['default' => UserRoleEnum::Guest]
        )]
        #[Sortable]
        private UserRoleEnum $role = UserRoleEnum::Guest,
        #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['default' => null])]
        #[Sortable]
        private ?string $firstName = null,
        #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['default' => null])]
        #[Sortable]
        private ?string $lastName = null,
        #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['default' => null])]
        private ?string $sessionId = null,
        #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
        #[Sortable]
        private bool $deleted = false,
        #[ORM\Column(type: Types::BOOLEAN, options: ['default' => true])]
        #[Sortable]
        private bool $enabled = true,
        #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
        #[Sortable]
        private ?\DateTimeImmutable $lastLoginAt = null,
    ) {
    }

    public function getUserName(): string
    {
        return $this->userName;
    }

    public function setUserName(string $userName): void
    {
        $this->userName = $userName;
    }

    public function getPasswordEncrypted(): ?string
    {
        return $this->password;
    }

    public function setPasswordEncrypted(string $encryptedPassword): void
    {
        $this->password = $encryptedPassword;
    }

    public function setPasswordFromPlaintext(#[\SensitiveParameter] string $passwordToEncrypt): void
    {
        $this->password = UserPasswordHelper::encrypt($passwordToEncrypt);
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getRole(): UserRoleEnum
    {
        return $this->role;
    }

    public function setRole(UserRoleEnum $role): void
    {
        $this->role = $role;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): void
    {
        $this->lastName = $lastName;
    }

    public function getFullName(): ?string
    {
        if (empty($this->firstName) && empty($this->lastName)) {
            return null;
        } elseif (empty($this->firstName)) {
            return $this->lastName;
        } elseif (empty($this->lastName)) {
            return $this->firstName;
        }

        return $this->firstName . ' ' . $this->lastName;
    }

    public function setFullName(string $firstName, string $lastName): void
    {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
    }

    public function getSessionId(): ?string
    {
        return $this->sessionId;
    }

    public function setSessionId(?string $sessionId): void
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

    public function getLastLoginAt(): ?\DateTimeImmutable
    {
        return $this->lastLoginAt;
    }

    public function setLastLoginAt(?\DateTimeImmutable $lastLoginAt): void
    {
        $this->lastLoginAt = $lastLoginAt;
    }
}
