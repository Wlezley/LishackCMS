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
     * Constructor for the UserRole class.
     * Converts a user object, role name, or role ID into the corresponding role level.
     *
     * @param \Nette\Security\User|string|int $value User object, role name, or role ID
     * @throws \InvalidArgumentException If the provided role name or ID is invalid
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

    /**
     * Converts the UserRole object to a string representation.
     *
     * @return string The role name corresponding to the current role level
     */
    public function __toString(): string
    {
        return self::USER_ROLES[$this->level];
    }

    /**
     * Retrieves the role level.
     *
     * @return int Role level
     */
    public function getLevel(): int
    {
        return $this->level;
    }

    /**
     * Retrieves the role name.
     *
     * @return string Role name
     */
    public function getName(): string
    {
        return self::USER_ROLES[$this->level];
    }

    /**
     * Returns a list of roles with lower levels.
     *
     * @param bool $includeEquals Whether to include the current role in the list
     * @return array<int,string> Array of role levels and names below the current role
     */
    public function getLowerList(bool $includeEquals = false): array
    {
        $roleList = [];

        foreach (self::USER_ROLES as $roleID => $roleName) {
            if ($roleID < $this->level) {
                $roleList[$roleID] = $roleName;
            }
        }

        if ($includeEquals) {
            $roleList[$this->level] = self::USER_ROLES[$this->level];
        }

        return $roleList;
    }

    /**
     * Returns a list of roles with higher levels.
     *
     * @param bool $includeEquals Whether to include the current role in the list
     * @return array<int,string> Array of role levels and names above the current role
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
    // ###             COMPARISON             ###
    // ##########################################

    /**
     * Checks if the current role matches the given role.
     *
     * @param string|int $role Role name or level to compare
     * @return bool True if the roles match, false otherwise
     */
    public function is(string|int $role): bool
    {
        return $this == new UserRole($role);
    }

    /**
     * Checks if the current role does not match the given role.
     *
     * @param string|int $role Role name or level to compare
     * @return bool True if the roles do not match, false otherwise
     */
    public function isNot(string|int $role): bool
    {
        return $this != new UserRole($role);
    }

    /**
     * Checks if the current role is in the provided list of roles.
     *
     * @param array<string|int> $roles Array of role names or levels to check against
     * @return bool True if the current role is in the list, false otherwise
     */
    public function isInArray(array $roles): bool
    {
        return in_array($this, $roles);
    }

    /**
     * Checks if the current role is greater than the given role.
     *
     * @param string|int $role Role name or level to compare
     * @return bool True if the current role is greater, false otherwise
     */
    public function isGreaterThan(string|int $role): bool
    {
        return $this > new UserRole($role);
    }

    /**
     * Checks if the current role is less than the given role.
     *
     * @param string|int $role Role name or level to compare
     * @return bool True if the current role is less, false otherwise
     */
    public function isLessThan(string|int $role): bool
    {
        return $this < new UserRole($role);
    }

    /**
     * Checks if the current role is greater than or equal to the given role.
     *
     * @param string|int $role Role name or level to compare
     * @return bool True if the current role is greater than or equal, false otherwise
     */
    public function isGreaterOrEqualsThan(string|int $role): bool
    {
        return $this >= new UserRole($role);
    }

    /**
     * Checks if the current role is less than or equal to the given role.
     *
     * @param string|int $role Role name or level to compare
     * @return bool True if the current role is less than or equal, false otherwise
     */
    public function isLessOrEqualsThan(string|int $role): bool
    {
        return $this <= new UserRole($role);
    }

    /**
     * Compares two roles using a given operator.
     *
     * @param \Nette\Security\User|string|int $left Left operand (user object, role name, or role ID)
     * @param string $operator Comparison operator (e.g., '==', '!=', '>', '>=', '<', '<=', '<>')
     * @param \Nette\Security\User|string|int $right Right operand (user object, role name, or role ID)
     * @return bool Result of the comparison
     * @throws \InvalidArgumentException If the operator is not supported
     */
    public static function compare(\Nette\Security\User|string|int $left, string $operator, \Nette\Security\User|string|int $right): bool
    {
        if ($left instanceof \Nette\Security\User) {
            $left = $left->getRoles()[0];
        }

        if ($right instanceof \Nette\Security\User) {
            $right = $right->getRoles()[0];
        }

        $l = new UserRole($left);
        $r = new UserRole($right);

        return match ($operator) { // Thanks to KsaRuu
            '=='        => $l == $r,
            '!=', '<>'  => $l != $r,
            '>'         => $l >  $r,
            '>='        => $l >= $r,
            '<'         => $l <  $r,
            '<='        => $l <= $r,
            default     => throw new \InvalidArgumentException("Unsupported comparison operator '$operator'."),
        };
    }

    // ##########################################
    // ###             VALIDATION             ###
    // ##########################################

    /**
     * Validates if the given role name exists.
     *
     * @param string $roleName Role name to validate
     * @return bool True if the role name exists, false otherwise
     */
    public static function isValidRoleName(string $roleName): bool
    {
        return in_array($roleName, self::USER_ROLES);
    }

    /**
     * Validates if the given role ID exists.
     *
     * @param int $roleID Role ID to validate
     * @return bool True if the role ID exists, false otherwise
     */
    public static function isValidRoleId(int $roleID): bool
    {
        return isset(self::USER_ROLES[$roleID]);
    }

    /**
     * Validates the given role (either name or ID).
     *
     * @param string|int $role Role name or ID to validate
     * @throws \InvalidArgumentException If the role is invalid
     */
    public static function assert(string|int $role): void
    {
        if (is_string($role)) {
            self::assertRoleName($role);
        } elseif (is_int($role)) {
            self::assertRoleId($role);
        }
    }

    /**
     * Asserts that the given role name exists.
     *
     * @param string $roleName Role name to validate
     * @throws \InvalidArgumentException If the role name is invalid
     */
    public static function assertRoleName(string $roleName): void
    {
        if (!in_array($roleName, self::USER_ROLES)) {
            throw new \InvalidArgumentException("Role '$roleName' was not found.");
        }
    }

    /**
     * Asserts that the given role ID exists.
     *
     * @param int $roleID Role ID to validate
     * @throws \InvalidArgumentException If the role ID is invalid
     */
    public static function assertRoleId(int $roleID): void
    {
        if (!isset(self::USER_ROLES[$roleID])) {
            throw new \InvalidArgumentException("Role ID '$roleID' was not found.");
        }
    }
}
