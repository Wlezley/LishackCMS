<?php

declare(strict_types=1);

namespace App\Models;

use Nette;

// use App\Models\Db;
use Nette\Database\Explorer;
use Nette\Security\AuthenticationException;
use Nette\Security\Passwords;
use Nette\Utils\Validators;


class User
{
    public const TABLE = 'users';

    /** @var Nette\Database\Explorer */
    protected $db;

    /** @var Passwords */
    private $passwords;

    public int $user_id;
    private array $data;

    public function __construct(Explorer $db, Passwords $passwords, int $user_id = null)
    {
        $this->db = $db;
        $this->passwords = $passwords;

        if ($user_id) {
            $this->load($user_id);
        }
    }

    public function load(int $user_id = null): void
    {
        if ($user_id) {
            $this->user_id = $user_id;
        }

        $selection = $this->db->table(User::TABLE)->where([
            'id' => $this->user_id,
            'enabled' => 1,
            'deleted' => 0
        ]);

        if ($selection->count() > 1) {
            throw new AuthenticationException("There are duplicate user ID:$this->user_id.");
        }

        if ($selection->count() == 0) {
            throw new AuthenticationException("User ID:$this->user_id not found.");
        }

        $this->data = $selection->fetch()->toArray();
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getSession(): array
    {
        return $this->data['session_id'];
    }

    public function createUser(string $username, string $password, string $role = 'user'): void
    {
        if (Validators::isNone($username)) {
            throw new AuthenticationException('User name is empty.');
        }

        if (Validators::isNone($password)) {
            throw new AuthenticationException('Password is empty.');
        }

        if ($this->db->table(User::TABLE)->select('id')->where(['name' => $username])->count() > 0) {
            throw new AuthenticationException('Duplicate user name.');
        }

        $this->db->table(User::TABLE)->insert([
            'name' => $username,
            'password' => $this->passwords->hash($password),
            // 'email' => NULL,
            'role' => $role,
            // 'full_name' => NULL,
            // 'session_id' => NULL,
            // 'deleted' => 0,
            // 'enabled' => 1,
        ]);
    }

    // TODO: for "delete" and "disable" command also remove sessions to force logout posible already logged acounts?
    public function deleteUser(string $username): void
    {
        if (Validators::isNone($username)) {
            throw new AuthenticationException('User name is empty.');
        }

        $selection = $this->db->table(User::TABLE)->select('id')->where([
            'name' => $username
        ]);

        if ($selection->count() > 1) {
            throw new AuthenticationException("Unable to delete account. There are duplicate user name '$username'.");
        }

        if ($selection->count() == 0) {
            throw new AuthenticationException("User '$username' not found.");
        }

        $selection->update([
            'deleted' => 1
        ]);

        // $this->logout();
    }

    // TODO: for "delete" and "disable" command also remove sessions to force logout posible already logged acounts?
    public function disableUser(string $username): void
    {
        if (Validators::isNone($username)) {
            throw new AuthenticationException('User name is empty.');
        }

        $selection = $this->db->table(User::TABLE)->select('id')->where([
            'name' => $username
        ]);

        if ($selection->count() > 1) {
            throw new AuthenticationException("Unable to disable account. There are duplicate user name '$username'.");
        }

        if ($selection->count() == 0) {
            throw new AuthenticationException("User '$username' not found.");
        }

        $selection->update([
            'enabled' => 0
        ]);

        // $this->logout();
    }

    public function enableUser(string $username): void
    {
        if (Validators::isNone($username)) {
            throw new AuthenticationException('User name is empty.');
        }

        $selection = $this->db->table(User::TABLE)->select('id')->where([
            'name' => $username
        ]);

        if ($selection->count() > 1) {
            throw new AuthenticationException("Unable to enable account. There are duplicate user name '$username'.");
        }

        if ($selection->count() == 0) {
            throw new AuthenticationException("User '$username' not found.");
        }

        $selection->update([
            'enabled' => 1
        ]);
    }

    public function renameUser(string $oldName, string $newName): void
    {
        if (Validators::isNone($oldName)) {
            throw new AuthenticationException('User old name is empty.');
        }

        if (Validators::isNone($newName)) {
            throw new AuthenticationException('User new name is empty.');
        }

        if ($this->db->table(User::TABLE)->select('id')->where(['name' => $newName])->count() > 0) {
            throw new AuthenticationException("Duplicate user name '$newName'.");
        }

        $selection = $this->db->table(User::TABLE)->select('id')->where([
            'name' => $oldName
        ]);

        if ($selection->count() == 0) {
            throw new AuthenticationException("User '$oldName' not found.");
        }

        $selection->update([
            'name' => $newName
        ]);
    }

    function setUserRole($username, $role) : void
    {
        if (Validators::isNone($username)) {
            throw new AuthenticationException('User name is empty.');
        }

        $selection = $this->db->table(User::TABLE)->select('id')->where([
            'name' => $username,
            'enabled' => 1,
            'deleted' => 0
        ]);

        if ($selection->count() > 1) {
            throw new AuthenticationException("Unable to change role. There are duplicate user name '$username'.");
        }

        if ($selection->count() == 0) {
            throw new AuthenticationException("User '$username' not found, or is disabled and/or marked as pending deletion.");
        }

        $selection->update([
            'role' => $role
        ]);
    }
}
