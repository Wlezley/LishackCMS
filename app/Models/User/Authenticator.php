<?php

declare(strict_types=1);

namespace App\Models;

use Nette;
use Nette\Database\Explorer;
use Nette\Http\Session;
use Nette\Security\AuthenticationException;
use Nette\Security\Passwords;
use Nette\Security\SimpleIdentity;

class Authenticator implements Nette\Security\Authenticator
{
    public function __construct(protected Explorer $db, private Session $session, private Passwords $passwords)
    {
    }

    public function authenticate(string $username, string $password): SimpleIdentity
    {
        $result = $this->db->table(UserManager::TABLE_NAME)->where([
                'name' => $username,
                'deleted' => 0,
                'enabled' => 1,
            ])->limit(1);
        $row = $result->fetch();

        if (!($row && $this->passwords->verify($password, $row->password))) {
            throw new AuthenticationException('Invalid credentials.', self::InvalidCredential);
        }

        $this->session->regenerateId();
        $sessionId = $this->session->getId();

        $user = [
            'id' => $row->id,
            'name' => $row->name,
            'email' => $row->email,
            'role' => $row->role,
            'full_name' => $row->full_name,
            'session_id' => $sessionId,
            'deleted' => $row->deleted,
            'enabled' => $row->enabled,
            'last_login' => Explorer::literal('NOW()'), // or Carbon::now()
        ];

        $result->update($user);

        return new SimpleIdentity($user['id'], $user['role'], $user);
    }
}
