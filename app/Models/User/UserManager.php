<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Helpers\ArrayHelper;
use App\Models\UserException;
use Nette\Security\Passwords;
use Nette\Utils\Validators;

class UserManager extends BaseModel
{
    public const TABLE_NAME = 'users';

    /** @var array<int,array<string,string|int|null>> $userList */
    protected mixed $userList = [];

    public function loadList(): void
    {
        $result = $this->db->table(self::TABLE_NAME)
            ->fetchAll();

        $this->userList = ArrayHelper::resultToArray($result);
    }

    /** @return array<int,array<string,string|int|null>> */
    public function getList(bool $forceReload = false): array
    {
        if (empty($this->userList) || $forceReload) {
            $this->load();
        }

        return $this->userList;
    }

    /** @return array<string,mixed> */
    public function get(int $id): array
    {
        $result = $this->db->table(self::TABLE_NAME)
            ->get($id);

        if (!$result) {
            throw new UserException("User ID: '$id' not found.");
        }

        return $result->toArray();
    }

    /** @return array<string,mixed> */
    public function getByName(string $name): array
    {
        $result = $this->db->table(self::TABLE_NAME)
            ->where('name', $name)
            ->fetch();

        if (!$result) {
            throw new UserException("User NAME: '$name' not found.");
        }

        return $result->toArray();
    }

    public function getIdByName(string $name): int
    {
        $result = $this->db->table(self::TABLE_NAME)
            ->select('id')
            ->where('name', $name)
            ->fetch();

        if (!$result) {
            throw new UserException("User NAME: '$name' not found.");
        }

        return $result['id'];
    }

    /** @param array<string,string|int|null> $data */
    public function create(array $data): int
    {
        if (Validators::isNone($data['name'])) {
            throw new UserException('User name is empty.');
        }
        if (Validators::isNone($data['password'])) {
            throw new UserException('Password is empty.');
        }
        if (!empty($data['email']) && !Validators::isEmail($data['email'])) {
            throw new UserException('Invalid email format.');
        }
        if ($this->db->table(self::TABLE_NAME)->select('id')->where(['name' => $data['name']])->count() > 0) {
            throw new UserException('Duplicate user name.');
        }

        $data = UserValidator::prepareData($data);
        UserValidator::validateData($data);

        $id = $this->db->table(self::TABLE_NAME)
            ->insert($data);

        // @phpstan-ignore property.nonObject
        return $id->id;
    }

    /** @param array<string,string|int|null> $data */
    public function update(int $id, array $data): bool
    {
        if ($id == 1) {
            throw new UserException('The main administrator account cannot be directly edited.');
        }

        UserValidator::validateData($data);

        $user = $this->db->table(self::TABLE_NAME)
            ->get($id);

        if (!$user) {
            throw new UserException("User ID '$id' not found.");
        }

        $result = $user->update($data);

        if ($result && isset($this->userList[$id])) {
            $this->userList[$id] = array_merge($this->userList[$id], $data);
        }

        return $result;
    }

    public function setEnabled(int $id, bool $enabled): bool
    {
        return $this->update($id, ['enabled' => (int)$enabled]);
    }

    public function setDeleted(int $id, bool $deleted): bool
    {
        return $this->update($id, ['deleted' => (int)$deleted]);
    }

    public function rename(int $id, string $newName): bool
    {
        return $this->update($id, ['name' => $newName]);
    }

    public function setPassword(int $id, #[\SensitiveParameter] string $password): bool
    {
        return $this->update($id, ['password' => (new Passwords(PASSWORD_BCRYPT, ['cost' => 12]))->hash($password)]);
    }

    public function setRole(int $id, string $role): bool
    {
        return $this->update($id, ['role' => $role]);
    }
}
