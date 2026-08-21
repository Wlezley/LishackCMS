<?php

declare(strict_types=1);

namespace App\Models\User;

use App\Entity\User\UserRepositoryInterface;
use Carbon\Carbon;
use Nette\Http\Session;
use Nette\Security\AuthenticationException;
use Nette\Security\Passwords;
use Nette\Security\SimpleIdentity;

readonly class Authenticator implements \Nette\Security\Authenticator
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private Session $session,
        private Passwords $passwords
    ) {
    }

    public function authenticate(string $username, #[\SensitiveParameter] string $password): SimpleIdentity
    {
        $user = $this->userRepository->findActiveUserByUserName($username);
        if ($user === null) {
            throw new AuthenticationException('Invalid credentials.', self::InvalidCredential);
        }

        $encryptedPassword = $user->getPasswordEncrypted();

        if (!$this->passwords->verify($password, $encryptedPassword)) {
            throw new AuthenticationException('Invalid credentials.', self::InvalidCredential);
        } elseif ($this->passwords->needsRehash($encryptedPassword)) {
            $user->setPasswordEncrypted($this->passwords->hash($password));

            // TODO: Tahle podmínka je divná...
        }

        $this->session->regenerateId();
        $sessionId = $this->session->getId();
        $lastLogin = Carbon::now();

        $user->setSessionId($sessionId);
        $user->setLastLoginAt($lastLogin->toDateTimeImmutable());
        $this->userRepository->save($user);

        // TODO: Add toArray() method to User entity ???
        $data = [
            'id' => $user->getId(),
            'user_name' => $user->getUserName(), // TODO: name ???
            'email' => $user->getEmail(),
            'role' => $user->getRole()->value,
            'full_name' => $user->getFullName(),
            'session_id' => $sessionId,
            'deleted' => $user->isDeleted(),
            'enabled' => $user->isEnabled(),
            'last_login' => $lastLogin,
        ];

        return new SimpleIdentity(
            id: $user->getId(),
            roles: $user->getRole()->value,
            data: $data
        );
    }
}
