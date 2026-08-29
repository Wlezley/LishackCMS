<?php

declare(strict_types=1);

namespace App\Service\User;

use App\Entity\User\User;
use App\Entity\User\UserRepository;
use App\Enum\UserRoleEnum;
use App\Helper\UserPasswordHelper;
use Webmozart\Assert\Assert;
use Webmozart\Assert\InvalidArgumentException;

readonly class UserService
{
    public function __construct(
        private UserRepository $userRepository,
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

    public function findByUserName(
        string $userName,
        ?bool $includeDisabled = null,
        ?bool $includeDeleted = null,
    ): ?User {
        return $this->userRepository->findByUserName(
            userName: $userName,
            filterDisabled: $includeDisabled,
            filterDeleted: $includeDeleted,
        );
    }

    /**
     * @throws InvalidArgumentException If the user is not found.
     */
    public function getIdByUserName(
        string $userName,
        ?bool $includeDisabled = null,
        ?bool $includeDeleted = null,
    ): int {
        $userId = $this->findByUserName(
            userName: $userName,
            includeDisabled: $includeDisabled,
            includeDeleted: $includeDeleted,
        )?->getId();

        Assert::notNull($userId, 'User not found');

        return $userId;
    }

    public function setPassword(
        User $user,
        #[\SensitiveParameter]
        string $newPassword
    ): void {
        $user->setPasswordFromPlaintext($newPassword);
        $this->userRepository->save($user);
    }

    public function rename(User $user, string $newName): void
    {
        $user->setUserName($newName);
        $this->userRepository->save($user);
    }

    public function setRole(User $user, UserRoleEnum $role): void
    {
        $user->setRole($role);
        $this->userRepository->save($user);
    }

    public function setEnabled(User $user, bool $isEnabled): void
    {
        $user->setEnabled($isEnabled);
        $this->userRepository->save($user);
    }

    public function setDeleted(User $user, bool $isDeleted): void
    {
        $user->setDeleted($isDeleted);
        $this->userRepository->save($user);
    }
}
