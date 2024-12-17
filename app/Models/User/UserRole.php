<?php

declare(strict_types=1);

namespace App\Models;

class UserRole
{
    public const USER_ROLES = [
        0 => 'guest',
        1 => 'user',
        2 => 'redactor',
        3 => 'manager',
        4 => 'admin'
    ];

    public int $level;

    /** @param string|int $value */
    public function __construct(string|int $value)
    {
        if (is_string($value)) {
            self::assertRoleName($value);
            $this->level = array_keys(self::USER_ROLES, $value)[0];
        } elseif (is_int($value)) {
            self::assertRoleId($value);
            $this->level = $value;
        }
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function getName(): string
    {
        return self::USER_ROLES[$this->level];
    }

    // ##########################################
    // ###             COMPARSION             ###
    // ##########################################

    public function isEquals(string|int $role): bool
    {
        return $this == new UserRole($role);
    }

    public function isGreaterThan(string|int $role): bool
    {
        return $this > new UserRole($role);
    }

    public function isLessThan(string|int $role): bool
    {
        return $this < new UserRole($role);
    }

    public function isGreaterOrEqualsThan(string|int $role): bool
    {
        return $this >= new UserRole($role);
    }

    public function isLessOrEqualsThan(string|int $role): bool
    {
        return $this <= new UserRole($role);
    }

    public static function compare(string|int $left, string $operator, string|int $right): bool
    {
        $a = new UserRole($left);
        $b = new UserRole($right);

        switch ($operator) {
            case '==': return $a == $b;
            case '>' : return $a >  $b;
            case '>=': return $a >= $b;
            case '<' : return $a <  $b;
            case '<=': return $a <= $b;
            case '<>': return $a <> $b;
            default: throw new \InvalidArgumentException('Unsupported comparsion operator.');
        }
    }

    // ##########################################
    // ###             VALIDATION             ###
    // ##########################################

    public static function isValidRoleName(string $roleName): bool
    {
        return in_array($roleName, self::USER_ROLES);
    }

    public static function isValidRoleId(int $roleID): bool
    {
        return isset(self::USER_ROLES[$roleID]);
    }

    public static function assert(string|int $role): void
    {
        if (is_string($role)) {
            self::assertRoleName($role);
        } elseif (is_int($role)) {
            self::assertRoleId($role);
        }
    }

    public static function assertRoleName(string $roleName): void
    {
        if (!in_array($roleName, self::USER_ROLES)) {
            throw new \InvalidArgumentException("Role '$roleName' was not found.");
        }
    }

    public static function assertRoleId(int $roleID): void
    {
        if (!isset(self::USER_ROLES[$roleID])) {
            throw new \InvalidArgumentException("Role ID '$roleID' was not found.");
        }
    }
}
