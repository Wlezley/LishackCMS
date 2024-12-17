<?php

declare(strict_types=1);

namespace App\Models\Helpers;

use Nette\Utils\Strings;

class StringHelper
{
    /**
     * Converts a string into a webalized format.
     *
     * This method transforms the input string into a "web-safe" format,
     * replacing spaces and special characters with hyphens and ensuring
     * only lowercase alphanumeric characters and hyphens remain.
     *
     * @param string $value The input string to be webalized.
     * @return string The webalized string.
     */
    public static function webalize(string $value): string
    {
        return Strings::webalize($value);
    }

    /**
     * Checks if a string is in a valid webalized format.
     *
     * A valid webalized string contains only lowercase letters, numbers, and hyphens (`-`).
     *
     * @param string $value The string to check.
     * @return bool True if the string is webalized, false otherwise.
     */
    public static function isWebalized(string $value): bool
    {
        return (bool) preg_match('/^[a-z0-9\-]+$/', $value);
    }

    /**
     * Asserts that a string is in a valid webalized format.
     *
     * Throws an exception if the input string does not meet the webalized format criteria.
     *
     * @param string $value The string to validate.
     * @param string $label A custom label for the variable in the error message. Defaults to 'variable'.
     *
     * @throws \InvalidArgumentException If the string is not webalized.
     */
    public static function assertWebalized(string $value, string $label = 'variable'): void
    {
        if (!self::isWebalized($value)) {
            throw new \InvalidArgumentException("The $label must be in a valid webalized format.");
        }
    }

    /**
     * Converts a string into a slug format.
     *
     * This method transforms the input string into a slug-safe format,
     * replacing spaces and special characters with underscores (`_`).
     *
     * @param string $value The input string to be slugized.
     * @return string The slugized string.
     */
    public static function slugize(string $value): string
    {
        return str_replace('-', '_', Strings::webalize($value));
    }

    /**
     * Checks if a string is in a valid slug format.
     *
     * A valid slug contains only lowercase letters, numbers, and underscores (`_`).
     *
     * @param string $value The string to check.
     * @return bool True if the string is a valid slug, false otherwise.
     */
    public static function isSlug(string $value): bool
    {
        return (bool) preg_match('/^[a-z0-9_]+$/', $value);
    }

    /**
     * Asserts that a string is in a valid slug format.
     *
     * Throws an exception if the input string does not meet the slug format criteria.
     *
     * @param string $value The string to validate.
     * @param string $label A custom label for the variable in the error message. Defaults to 'variable'.
     *
     * @throws \InvalidArgumentException If the string is not a valid slug.
     */
    public static function assertSlug(string $value, string $label = 'variable'): void
    {
        if (!self::isSlug($value)) {
            throw new \InvalidArgumentException("The $label must be in a valid slug format.");
        }
    }

    /**
     * Checks if a string is a valid JSON.
     *
     * This method validates whether the given string is properly formatted as a JSON.
     * It uses `json_decode` and checks for any parsing errors.
     *
     * @param string $value The string to check.
     * @return bool True if the string is a valid JSON, false otherwise.
     */
    public static function isJson(string $value): bool
    {
        json_decode($value);
        return json_last_error() === JSON_ERROR_NONE;
    }
}
