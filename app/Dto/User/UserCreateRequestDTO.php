<?php

declare(strict_types=1);

namespace App\Dto\User;

use App\Enum\UserRoleEnum;
use Webmozart\Assert\Assert;

class UserCreateRequestDTO
{
    public function __construct(
        public string $userName,
        public string $email,
        #[\SensitiveParameter]
        public string $password,
        public UserRoleEnum $role = UserRoleEnum::Guest,
        public ?string $firstName = null,
        public ?string $lastName = null,
        public ?string $sessionId = null,
        public bool $deleted = false,
        public bool $enabled = true,
    ) {
    }

    public static function init(
        string $userName = '',
        string $email = '',
        #[\SensitiveParameter]
        string $password = '',
        UserRoleEnum $role = UserRoleEnum::Guest,
        ?string $firstName = null,
        ?string $lastName = null,
        ?string $sessionId = null,
        bool $deleted = false,
        bool $enabled = true,
    ): self {
        return new self(
            userName: $userName,
            email: $email,
            password: $password,
            role: $role,
            firstName: $firstName,
            lastName: $lastName,
            sessionId: $sessionId,
            deleted: $deleted,
            enabled: $enabled,
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        Assert::keyExists($data, 'userName');
        Assert::keyExists($data, 'email');
        Assert::keyExists($data, 'password');

        return new self(
            userName: $data['userName'],
            email: $data['email'],
            password: $data['password'],
            role: UserRoleEnum::tryFrom($data['role']) ?? UserRoleEnum::Guest,
            firstName: $data['firstName'] ?? null,
            lastName: $data['lastName'] ?? null,
            sessionId: $data['sessionId'] ?? null,
            deleted: $data['deleted'] ?? false,
            enabled: $data['enabled'] ?? true,
        );
    }
}
