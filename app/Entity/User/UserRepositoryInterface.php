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
    public function getById(int $userId): ?User;

    public function getByUserName(string $userName): ?User;

    public function getIdByUserName(string $userName): ?int;

    /**
     * @param array<string, mixed> $criteria
     * @return User[]
     */
    public function getAllUsers(array $criteria = []): array;

    public function findActiveUserByUserName(string $username): ?User;

    /**
     * @throws \Exception
     */
    public function rename(int $userId, string $newName): void;

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

    /**
     * @throws \Exception
     */
    public function setPassword(int $userId, #[\SensitiveParameter] string $password): void;

    /**
     * @throws \Exception
     */
    public function setRole(int $userId, UserRoleEnum $role): void;

    /**
     * @throws \Exception
     */
    public function setEnabled(int $userId, bool $isEnabled): void;

    /**
     * @throws \Exception
     */
    public function setDeleted(int $userId, bool $isDeleted): void;
}
