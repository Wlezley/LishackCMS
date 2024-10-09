<?php

declare(strict_types=1);

namespace App\Models;

use Nette;

use Nette\Database\Explorer;
use Nette\Security\AuthenticationException;
use Nette\Security\Passwords;
use Nette\Security\SimpleIdentity;


class Authenticator implements Nette\Security\Authenticator
{
    /** @var Nette\Database\Explorer */
    protected $db;

    // /** @var Nette\Http\Request */
    // private $request;

    // /** @var Nette\Http\Session */
    // private $session;

    /** @var Nette\Security\Passwords */
    private $passwords;

    public function __construct(Explorer $db, Passwords $passwords)
    {
        $this->db = $db;
        // $this->request = $request;
        // $this->session = $session;
        $this->passwords = $passwords;
    }

    public function authenticate(string $username, string $password): SimpleIdentity
    {
        $row = $this->db->table(User::TABLE_NAME)->where('name', $username)->fetch();

        if (!($row && $this->passwords->verify($password, $row->password))) {
            throw new AuthenticationException('Invalid credentials.', self::InvalidCredential);
        }

        $user = $row->toArray();
        unset($user['password']);

        // TODO: Session handler ?

        return new SimpleIdentity($user['id'], $user['role'], $user);
    }
}
