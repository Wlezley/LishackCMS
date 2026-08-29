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

    public function getById(int $userId): ?User
    {
        return $this->findById($userId);
    }

    public function findByUserName(
        string $userName,
        ?bool $filterDisabled = null,
        ?bool $filterDeleted = null,
    ): ?User {
        $qb = $this->entityManager->createQueryBuilder();

        $qb->select('u')
            ->from(User::class, 'u')
            ->where('u.userName = :userName')
            ->setParameter('userName', $userName);

        if ($filterDisabled !== null) {
            $qb->andWhere('u.enabled != :enabled')
                ->setParameter('enabled', $filterDisabled);
        }

        if ($filterDeleted !== null) {
            $qb->andWhere('u.deleted != :deleted')
                ->setParameter('deleted', $filterDeleted);
        }

        return $qb->getQuery()->getOneOrNullResult();
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
}
