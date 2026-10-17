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

    public function setUserName(string $userName): self
    {
        $this->userName = $userName;
        return $this;
    }

    public function getPasswordEncrypted(): ?string
    {
        return $this->password;
    }

    public function setPasswordEncrypted(string $encryptedPassword): self
    {
        $this->password = $encryptedPassword;
        return $this;
    }

    public function setPasswordFromPlaintext(#[\SensitiveParameter] string $passwordToEncrypt): self
    {
        $this->password = UserPasswordHelper::encrypt($passwordToEncrypt);
        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getRole(): UserRoleEnum
    {
        return $this->role;
    }

    public function setRole(UserRoleEnum $role): self
    {
        $this->role = $role;
        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): self
    {
        $this->firstName = $firstName;
        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): self
    {
        $this->lastName = $lastName;
        return $this;
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

    public function setFullName(string $firstName, string $lastName): self
    {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        return $this;
    }

    public function getSessionId(): ?string
    {
        return $this->sessionId;
    }

    public function setSessionId(?string $sessionId): self
    {
        $this->sessionId = $sessionId;
        return $this;
    }

    public function isDeleted(): bool
    {
        return $this->deleted;
    }

    public function setDeleted(bool $deleted): self
    {
        $this->deleted = $deleted;
        return $this;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;
        return $this;
    }

    public function getLastLoginAt(): ?\DateTimeImmutable
    {
        return $this->lastLoginAt;
    }

    public function setLastLoginAt(?\DateTimeImmutable $lastLoginAt): self
    {
        $this->lastLoginAt = $lastLoginAt;
        return $this;
    }
}
