<?php

declare(strict_types=1);

namespace App\Helper;

use Nette\Security\Passwords;

class UserPasswordHelper
{
    private const string Algo = PASSWORD_BCRYPT;
    private const array Options = [
        'cost' => 12,
    ];

    public static function encrypt(string $password): string
    {
        return new Passwords(self::Algo, self::Options)->hash($password);
    }

    public static function tryEncrypt(?string $password): ?string
    {
        if ($password === null) {
            return null;
        }

        return new Passwords(self::Algo, self::Options)->hash($password);
    }

    public static function verify(string $password, string $hash): bool
    {
        return new Passwords(self::Algo, self::Options)->verify($password, $hash);
    }

    public static function needsRehash(string $hash): bool
    {
        return new Passwords(self::Algo, self::Options)->needsRehash($hash);
    }
}
