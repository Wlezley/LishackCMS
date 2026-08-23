<?php

declare(strict_types=1);

namespace App\Service\User;

use App\Entity\User\User;
use App\Entity\User\UserRepository;
use App\Enum\UserRoleEnum;
use App\Helper\UserPasswordHelper;

class UserService
{
    public function __construct(
        private readonly UserRepository $userRepository,
    ) {
    }

    public function create(
        string $userName,
        string $email,
        #[\SensitiveParameter]
        ?string $password = null,
        UserRoleEnum $role = UserRoleEnum::Guest,
        ?string $firstName = null,
        ?string $lastName = null,
        ?string $sessionId = null,
        bool $deleted = false,
        bool $enabled = true,
    ): User {
        return $this->userRepository->create(
            userName: $userName,
            email: $email,
            encryptedPassword: UserPasswordHelper::tryEncrypt($password),
            role: $role,
            firstName: $firstName,
            lastName: $lastName,
            sessionId: $sessionId,
            deleted: $deleted,
            enabled: $enabled,
        );
    }
}
