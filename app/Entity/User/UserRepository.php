<?php

declare(strict_types=1);

namespace App\Entity\User;

use App\Entity\BaseRepository;
use App\Enum\UserRoleEnum;
use Nette\Security\Passwords;

/**
 * @extends BaseRepository<User>
 */
final readonly class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    public function getById(int $userId): ?User
    {
        return $this->findById($userId);
    }

    public function getIdByUserName(string $userName): int
    {
        $user = $this->findOneBy(['userName' => $userName]);

        return $user->getId();
    }

    /**
     * @throws \Exception
     */
    public function rename(int $userId, string $newName): void
    {
        $user = $this->findById($userId);
        if ($user === null) {
            throw new \Exception('User not found');
        }

        $user->setUserName($newName);
        $user->save();
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
        $user = new User(
            userName: $userName,
            password: new Passwords(PASSWORD_BCRYPT, ['cost' => 12])->hash($password), // TODO: Password helper
            email: $email,
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
     * @throws \Exception
     */
    public function setPassword(int $userId, #[\SensitiveParameter] string $password): void
    {
        $user = $this->findById($userId);
        if ($user === null) {
            throw new \Exception('User not found');
        }

        $cryptedPassword = new Passwords(PASSWORD_BCRYPT, ['cost' => 12])->hash($password);

        $user->setPassword($cryptedPassword);
        $user->save();
    }

    /**
     * @throws \Exception
     */
    public function setRole(int $userId, UserRoleEnum $role): void
    {
        $user = $this->findById($userId);
        if ($user === null) {
            throw new \Exception('User not found');
        }

        $user->setRole($role);
        $user->save();
    }
}
