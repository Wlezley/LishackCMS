<?php

declare(strict_types=1);

namespace App\Service\User;

use App\Entity\User\User;
use App\Entity\User\UserRepository;
use App\Enum\UserRoleEnum;
use Nette\Security\Passwords;

class UserService
{
    public function __construct(
        private readonly UserRepository $userRepository,
    ) {
    }

    public function create(
        string $userName,
        #[\SensitiveParameter]
        string $password,
        string $email,
        UserRoleEnum $role,
        ?string $firstName,
        ?string $lastName,
        string $sessionId,
        bool $deleted = false,
        bool $enabled = true,
    ): User {
        return $this->userRepository->create(
            userName: $userName,
            encryptedPassword: new Passwords(PASSWORD_BCRYPT, ['cost' => 12])->hash($password), // TODO: Password helper
            email: $email,
            role: $role,
            firstName: $firstName,
            lastName: $lastName,
            sessionId: $sessionId,
            deleted: $deleted,
            enabled: $enabled,
        );
    }
}
