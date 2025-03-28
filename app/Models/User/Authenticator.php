<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Nette\Database\Explorer;
use Nette\Http\Session;
use Nette\Security\AuthenticationException;
use Nette\Security\Passwords;
use Nette\Security\SimpleIdentity;

class Authenticator implements \Nette\Security\Authenticator
{
    public function __construct(
        protected Explorer $db,
        private Session $session,
        private Passwords $passwords
    ) {}

    public function authenticate(string $username, #[\SensitiveParameter] string $password): SimpleIdentity
    {
        $user = $this->db->table(UserManager::TABLE_NAME)
            ->where(['name' => $username, 'deleted' => 0, 'enabled' => 1])
            ->limit(1)
            ->fetch();

        if (!($user && $this->passwords->verify($password, $user['password']))) {
            throw new AuthenticationException('Invalid credentials.', self::InvalidCredential);
        } elseif ($this->passwords->needsRehash($user['password'])) {
            $user->update(['password' => $this->passwords->hash($password)]);
        }

        $this->session->regenerateId();
        $sessionId = $this->session->getId();
        $lastLogin = Carbon::now();

        $user->update([
            'session_id' => $sessionId,
            'last_login' => $lastLogin
        ]);

        $data = [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role'],
            'full_name' => $user['full_name'],
            'session_id' => $sessionId,
            'deleted' => $user['deleted'],
            'enabled' => $user['enabled'],
            'last_login' => $lastLogin
        ];

        return new SimpleIdentity($user['id'], $user['role'], $data);
    }
}
