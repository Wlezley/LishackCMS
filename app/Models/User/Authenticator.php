<?php

declare(strict_types=1);

namespace App\Models;

use Nette;

use Nette\Database\Explorer;
use Nette\Security\Passwords;
use Nette\Security\AuthenticationException;
use Nette\Security\SimpleIdentity;


class Authenticator implements Nette\Security\Authenticator
{
    /** @var Nette\Database\Explorer */
    protected $db;

    /** @var Passwords */
    private $passwords;

    public function __construct(Explorer $db, Passwords $passwords)
    {
        $this->db = $db;
        $this->passwords = $passwords;
    }

    public function authenticate(string $username, string $password): SimpleIdentity
    {
        $row = $this->db->table(User::TABLE)->where('name', $username)->fetch();

        if (!($row && $this->passwords->verify($password, $row->password))) {
            throw new AuthenticationException('Nesprávné přihlašovací údaje');
        }

        $user = $row->toArray();
        unset($user['password']);

        return new SimpleIdentity($user['id'], $user['role'], $user);
    }

    public function addUser(string $username, string $password, string $role = 'user'): void
    {
        $this->db->table(User::TABLE)->insert([
            'name' => $username,
            'password' => $this->passwords->hash($password),
            'role'	   => $role
        ]);
    }
}
