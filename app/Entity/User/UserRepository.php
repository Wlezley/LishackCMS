<?php

declare(strict_types=1);

namespace App\Entity\User;

use App\Entity\BaseRepository;
use App\Enum\UserRoleEnum;

/**
 * @extends BaseRepository<User>
 */
final readonly class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    public function getById(int $userId): ?User
    {
        return $this->findById($userId);
    }

    public function getByUserName(string $userName): ?User
    {
        return $this->findOneBy(['userName' => $userName]);
    }

    // TODO: Move to UserService ???
    public function getIdByUserName(string $userName): ?int
    {
        return $this->getByUserName($userName)?->getId();
    }

    /**
     * @inheritDoc
     */
    public function getAllUsers(array $criteria = []): array
    {
        return $this->findBy(criteria: $criteria);
    }

    public function findActiveUserByUserName(string $username): ?User
    {
        return $this->findOneBy(['userName' => $username, 'deleted' => 0, 'enabled' => 1]);
    }

    /**
     * @inheritDoc
     */
    public function rename(int $userId, string $newName): void
    {
        $user = $this->findById($userId);
        if ($user === null) {
            throw new \Exception('User not found');
        }

        $user->setUserName($newName);
        $this->save($user);
    }

    public function create(
        string $userName,
        string $email,
        ?string $encryptedPassword = null,
        UserRoleEnum $role = UserRoleEnum::Guest,
        ?string $firstName = null,
        ?string $lastName = null,
        ?string $sessionId = null,
        bool $deleted = false,
        bool $enabled = true,
    ): User {
        $user = new User(
            userName: $userName,
            email: $email,
            password: $encryptedPassword,
            role: $role,
            firstName: $firstName,
            lastName: $lastName,
            sessionId: $sessionId,
            deleted: $deleted,
            enabled: $enabled,
        );

        $this->save($user);

        return $user;
    }

    /**
     * @inheritDoc
     */
    public function setPassword(int $userId, #[\SensitiveParameter] string $password): void
    {
        $user = $this->findById($userId);
        if ($user === null) {
            throw new \Exception('User not found');
        }

        $user->setPasswordFromPlaintext($password);
        $this->save($user);
    }

    /**
     * @inheritDoc
     */
    public function setRole(int $userId, UserRoleEnum $role): void
    {
        $user = $this->findById($userId);
        if ($user === null) {
            throw new \Exception('User not found');
        }

        $user->setRole($role);
        $this->save($user);
    }

    /**
     * @inheritDoc
     */
    public function setEnabled(int $userId, bool $isEnabled): void
    {
        $user = $this->findById($userId);
        if ($user === null) {
            throw new \Exception('User not found');
        }

        $user->setEnabled($isEnabled);
        $this->save($user);
    }

    /**
     * @inheritDoc
     */
    public function setDeleted(int $userId, bool $isDeleted): void
    {
        $user = $this->findById($userId);
        if ($user === null) {
            throw new \Exception('User not found');
        }

        $user->setDeleted($isDeleted);
        $this->save($user);
    }
}
