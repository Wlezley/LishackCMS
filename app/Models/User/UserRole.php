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

    /**
     * @param \Nette\Security\User|string|int $value
     */
    public function __construct(\Nette\Security\User|string|int $value)
    {
        if ($value instanceof \Nette\Security\User) {
            $value = $value->getRoles()[0];
        }

        if (is_string($value)) {
            self::assertRoleName($value);
            $this->level = array_keys(self::USER_ROLES, $value)[0];
        } elseif (is_int($value)) {
            self::assertRoleId($value);
            $this->level = $value;
        }
    }

    public function __toString(): string
    {
        return self::USER_ROLES[$this->level];
    }

    /**
     * @return int Role level
     */
    public function getLevel(): int
    {
        return $this->level;
    }

    /**
     * @return string Role name
     */
    public function getName(): string
    {
        return self::USER_ROLES[$this->level];
    }

    /**
     * @param bool $includeEquals Includes own role in the list
     * @return array<int,string> List of roles below (lower level)
     */
    public function getLowerList(bool $includeEquals = false): array
    {
        $roleList = [];

        if ($includeEquals) {
            $roleList[$this->level] = self::USER_ROLES[$this->level];
        }

        foreach (self::USER_ROLES as $roleID => $roleName) {
            if ($roleID < $this->level) {
                $roleList[$roleID] = $roleName;
            }
        }

        return $roleList;
    }

    /**
     * @param bool $includeEquals Includes own role in the list
     * @return array<int,string> List of roles above (higher level)
     */
    public function getHigherList(bool $includeEquals = false): array
    {
        $roleList = [];

        if ($includeEquals) {
            $roleList[$this->level] = self::USER_ROLES[$this->level];
        }

        foreach (self::USER_ROLES as $roleID => $roleName) {
            if ($roleID > $this->level) {
                $roleList[$roleID] = $roleName;
            }
        }

        return $roleList;
    }

    // ##########################################
    // ###             COMPARSION             ###
    // ##########################################

    public function is(string|int $role): bool
    {
        return $this == new UserRole($role);
    }

    public function isNot(string|int $role): bool
    {
        return $this != new UserRole($role);
    }

    /** @param array<string|int> $roles */
    public function isInArray(array $roles): bool
    {
        return in_array($this, $roles);
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

    public static function compare(\Nette\Security\User|string|int $left, string $operator, \Nette\Security\User|string|int $right): bool
    {
        if ($left instanceof \Nette\Security\User) {
            $left = $left->getRoles()[0];
        }

        if ($right instanceof \Nette\Security\User) {
            $right = $right->getRoles()[0];
        }

        $a = new UserRole($left);
        $b = new UserRole($right);

        switch ($operator) {
            case '==': return $a == $b;
            case '!=': return $a != $b;
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
