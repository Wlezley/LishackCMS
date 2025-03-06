<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Helpers\ArrayHelper;
use App\Models\Helpers\DatetimeHelper;
use Nette\Security\Passwords;
use Nette\Utils\Validators;

class UserValidator
{
    public const COLUMNS = [
        'id',
        'name',
        'password',
        'email',
        'role',
        'full_name',
        'session_id',
        'deleted',
        'enabled',
        'created',
        'last_login'
    ];

    /**
     * Builds a structured array of user data with default values.
     *
     * @param string $name The username.
     * @param string $password The user password (plaintext, hashed externally if needed).
     * @param string $role The user role. Defaults to 'user'.
     * @param string|null $email The user's email address.
     * @param string|null $fullName The user's full name. Defaults to the username if null.
     * @param bool $enabled Whether the user is enabled. Defaults to true.
     * @param bool $deleted Whether the user is marked as deleted. Defaults to false.
     *
     * @return array<string,string|int|null> An associative array containing user data.
     */
    public static function buildData(string $name, #[\SensitiveParameter] string $password, string $role = 'user', ?string $email = null, ?string $fullName = null, bool $enabled = true, bool $deleted = false): array
    {
        return [
            // 'id' => $id, // ID is not included in the built data
            'name' => $name,
            'password' => $password,
            'email' => $email,
            'role' => $role,
            'full_name' => $fullName ?? $name,
            // 'session_id' => null,
            'deleted' => $deleted ? 1 : 0,
            'enabled' => $enabled ? 1 : 0,
            // 'created' => null,
            // 'last_login' => null,
        ];
    }

    /**
     * Prepares user data for storage or further processing.
     *
     * Ensures consistency in the user data format, including hashing passwords
     * if required and normalizing boolean-like fields (`deleted`, `enabled`) to integers.
     *
     * @param array<string,string|int|null> $data The raw user data array.
     * @param bool $createPasswordHash Whether to hash the password. Defaults to true.
     * @return array<string,string|int|null> The prepared user data array.
     */
    public static function prepareData(array $data, bool $createPasswordHash = true): array
    {
        $name = $data['name'] ?? '';
        $password = $data['password'] ?? '';

        if ($createPasswordHash && !empty($password)) {
            $password = (new Passwords(PASSWORD_BCRYPT, ['cost' => 12]))->hash($password);
        }

        if (is_string($data['deleted'])) {
            $data['deleted'] = (int)$data['deleted'];
        }

        if (is_string($data['enabled'])) {
            $data['enabled'] = (int)$data['enabled'];
        }

        return [
            // 'id' => $data['id'] ?? null, // ID is not included in the prepared data
            'name' => $name,
            'password' => $password,
            'email' => $data['email'] ?? null,
            'role' => $data['role'] ?? 'user',
            'full_name' => $data['full_name'] ?? $name,
            // 'session_id' => $data['session_id'] ?? null,
            'deleted' => in_array($data['deleted'], [0, 1], true) ? $data['deleted'] : 0,
            'enabled' => in_array($data['enabled'], [0, 1], true) ? $data['enabled'] : 1,
            // 'created' => $data['created'] ?? Carbon::now()->format('Y-m-d H:i:s'),
            // 'last_login' => $data['last_login'] ?? null,
        ];
    }

    /**
     * Validates user data against expected formats and constraints.
     *
     * @param array<string,string|int|null> $data The user data array to validate.
     * @throws \InvalidArgumentException If validation fails for any field.
     */
    public static function validateData(array $data): void
    {
        ArrayHelper::assertExtraKeys(self::COLUMNS, $data, 'UserData');

        if (isset($data['id'])) {
            Validators::assert($data['id'], 'numericint', 'ID');
        }
        if (isset($data['name'])) {
            Validators::assert($data['name'], 'string:1..50', 'User Name');
        }
        if (isset($data['password'])) {
            Validators::assert($data['password'], 'string:1..255', 'Password');
        }
        if (isset($data['email']) && !empty($data['email'])) {
            Validators::assert($data['email'], 'string:1..255', 'E-mail');
            Validators::assert($data['email'], 'email', 'E-mail');
        }
        if (isset($data['role'])) {
            Validators::assert($data['role'], 'string:1..50', 'Role');
            UserRole::assertRoleName($data['role']);
        }
        if (isset($data['full_name'])) {
            Validators::assert($data['full_name'], 'string:1..150', 'Full Name');
        }
        if (isset($data['session_id'])) {
            Validators::assert($data['session_id'], 'string:1..150', 'Session ID');
        }
        if (isset($data['deleted']) && !in_array($data['deleted'], [0, 1], true)) {
            throw new \InvalidArgumentException('Deleted value must be either "0" or "1".');
        }
        if (isset($data['enabled']) && !in_array($data['enabled'], [0, 1], true)) {
            throw new \InvalidArgumentException('Enabled value must be either "0" or "1".');
        }

        // TODO: DateValidator (?)
        // if (isset($data['created'])) {
        //     DatetimeHelper::assertMySQLDatetime($data['created'], 'Created');
        // }
        // if (isset($data['last_login'])) {
        //     DatetimeHelper::assertMySQLDatetime($data['last_login'], 'Last Login');
        // }
    }
}
