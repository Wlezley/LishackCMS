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

    // TODO: Handle this in the UserRoleLevelEnum ???
    public static function getRoleLevel(string $role): ?int
    {
        return match ($role) {
            self::Guest->value => 0,
            self::User->value => 1,
            self::Redactor->value => 2,
            self::Manager->value => 3,
            self::Admin->value => 4,
            default => null,
        };
    }

    /**
     * @return array<int, string>
     */
    public static function toArray(): array
    {
        return array_column(self::cases(), 'value');
    }
}
