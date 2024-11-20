<?php

declare(strict_types=1);

namespace App\Models;

use Nette\Database\Explorer;
use Nette\Security\AuthenticationException;
use Nette\Security\Passwords;
use Nette\Utils\Validators;

class UserManager
{
    public const TABLE_NAME = 'users';

    private ?int $id;
    private array $data;
    private bool $isLoaded;

    public function __construct(protected Explorer $db, private Passwords $passwords)
    {
        $this->id = null;
        $this->data = [];
        $this->isLoaded = false;
    }

    public function load(int $id): void
    {
        $this->id = $id;
        $selection = $this->db->table(self::TABLE_NAME)->where(['id' => $this->id]);

        if ($selection->count() == 0) {
            throw new AuthenticationException("User ID: $this->id not found.");
        }

        $this->data = $selection->fetch()->toArray();
        $this->isLoaded = true;
    }

    public function create(string $name, string $password, string $role = 'user', string $email = '', string $full_name = ''): void
    {
        if (Validators::isNone($name)) {
            throw new AuthenticationException('User name is empty.');
        }

        if (Validators::isNone($password)) {
            throw new AuthenticationException('Password is empty.');
        }

        if (!Validators::isNone($email) && Validators::isEmail($email)) {
            throw new AuthenticationException('Invalid email format.');
        }

        if ($this->db->table(self::TABLE_NAME)->select('id')->where(['name' => $name])->count() > 0) {
            throw new AuthenticationException('Duplicate user name.');
        }

        $this->db->table(self::TABLE_NAME)->insert([
            'name' => $name,
            'password' => $this->passwords->hash($password),
            'email' => $email,
            'role' => $role,
            'full_name' => $full_name
        ]);
    }

    private function updateColumn(string|array $condition, string $column, mixed $value): void
    {
        if (Validators::isNone($column)) {
            throw new AuthenticationException('Column name is empty.');
        }

        $selection = $this->db->table(self::TABLE_NAME)->select('id')->where($condition);

        if ($selection->count() == 0) {
            throw new AuthenticationException('User not found.');
        }

        $selection->update([$column => $value]);
    }

    public function delete(string $name): void
    {
        if (Validators::isNone($name)) {
            throw new AuthenticationException('User name is empty.');
        }

        $this->updateColumn(['name' => $name], 'deleted', 1);
    }

    public function disable(string $name): void
    {
        if (Validators::isNone($name)) {
            throw new AuthenticationException('User name is empty.');
        }

        $this->updateColumn(['name' => $name], 'enabled', 0);
    }

    public function enable(string $name): void
    {
        if (Validators::isNone($name)) {
            throw new AuthenticationException('User name is empty.');
        }

        $this->updateColumn(['name' => $name], 'enabled', 1);
    }

    public function rename(string $oldName, string $newName): void
    {
        if (Validators::isNone($oldName)) {
            throw new AuthenticationException('Old user name is empty.');
        }

        if (Validators::isNone($newName)) {
            throw new AuthenticationException('New user name is empty.');
        }

        $this->updateColumn(['name' => $oldName], 'name', $newName);
    }

    public function setRole(string $name, string $role): void
    {
        if (Validators::isNone($name)) {
            throw new AuthenticationException('User name is empty.');
        }

        $this->updateColumn(['name' => $name], 'role', $role);
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getSession(): ?string
    {
        if ($this->isLoaded && isset($this->data['session_id'])) {
            return $this->data['session_id'];
        }

        return null;
    }
}
