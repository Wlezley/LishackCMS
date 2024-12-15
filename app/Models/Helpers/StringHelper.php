<?php

declare(strict_types=1);

namespace App\Models\Helpers;

use Nette\Utils\Strings;

class StringHelper
{
    public static function webalize(string $value): string
    {
        return Strings::webalize($value);
    }

    public static function isWebalized(string $value): bool
    {
        return (bool) preg_match('/^[a-z0-9\-]+$/', $value);
    }

    public static function assertWebalized(string $value, string $label = 'variable'): void
    {
        if (self::isWebalized($value)) {
            throw new \InvalidArgumentException("The $label must be in a valid webalized format.");
        }
    }


    public static function slugize(string $value): string
    {
        return str_replace('-', '_', Strings::webalize($value));
    }

    public static function isSlug(string $value): bool
    {
        return (bool) preg_match('/^[a-z0-9_]+$/', $value);
    }

    public static function assertSlug(string $value, string $label = 'variable'): void
    {
        if (self::isSlug($value)) {
            throw new \InvalidArgumentException("The $label must be in a valid slug format.");
        }
    }


    public static function isJson(string $value): bool
    {
        json_decode($value);
        return json_last_error() === JSON_ERROR_NONE;
    }
}
