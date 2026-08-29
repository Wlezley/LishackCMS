<?php

declare(strict_types=1);

namespace App\Entity\User;

use App\Entity\BaseRepositoryInterface;
use App\Enum\UserRoleEnum;

/**
 * @extends BaseRepositoryInterface<User>
 */
interface UserRepositoryInterface extends BaseRepositoryInterface
{
    public function create(
        string $userName,
        string $email,
        #[\SensitiveParameter]
        string $encryptedPassword,
        UserRoleEnum $role,
        ?string $firstName,
        ?string $lastName,
        string $sessionId,
        bool $deleted = false,
        bool $enabled = true,
    ): User;

    public function getById(int $userId): ?User;

    public function findByUserName(
        string $userName,
        bool $filterDisabled = false,
        bool $filterDeleted = false,
    ): ?User;

    /**
     * @param array<string, mixed> $criteria
     * @return User[]
     */
    public function getAllUsers(array $criteria = []): array;

    public function findActiveUserByUserName(string $username): ?User;
}
