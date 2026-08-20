<?php

declare(strict_types=1);

namespace App\Enum;

enum UserRoleEnum: string
{
    public const int MAX_LENGTH = 8; // TODO: Create PHPunit test for all arrays with this setting

    case Guest = 'guest';       // 0
    case User = 'user';         // 1
    case Redactor = 'redactor'; // 2
    case Manager = 'manager';   // 3
    case Admin = 'admin';       // 4

    public function getLevel(): int
    {
        return match ($this) {
            self::Guest => 0,
            self::User => 1,
            self::Redactor => 2,
            self::Manager => 3,
            self::Admin => 4,
        };
    }

    public static function fromLevel(int $level): self
    {
        return self::tryFromLevel($level)
            ?? throw new \ValueError(
                sprintf('Invalid user role level "%d".', $level),
            );
    }

    public static function tryFromLevel(int $level): ?self
    {
        return array_find(
            self::cases(),
            fn(self $role) => $role->getLevel() === $level,
        );
    }

    /**
     * @return array<int, string> Array of roles, indexed by their level
     */
    public static function toArray(): array
    {
        $roles = [];
        foreach (self::cases() as $role) {
            $roles[$role->getLevel()] = $role->value;
        }

        return $roles;
    }
}
