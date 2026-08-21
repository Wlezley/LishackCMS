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

    public function getIdByUserName(string $userName): int;

    /**
     * @throws \Exception
     */
    public function rename(int $userId, string $newName): void;

    /**
     * @throws \Exception
     */
    public function setPassword(int $userId, #[\SensitiveParameter] string $password): void;

    /**
     * @throws \Exception
     */
    public function setRole(int $userId, UserRoleEnum $role): void;
}
