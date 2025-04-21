<?php

declare(strict_types=1);

namespace App\Models\Helpers;

class SqlHelper
{
    private const SQL_RESERVED_WORDS = [
        'SELECT', 'INSERT', 'UPDATE', 'DELETE', 'DROP', 'TRUNCATE', 'ALTER', 'CREATE',
        'FROM', 'WHERE', 'HAVING', 'JOIN', 'ON', 'INTO', 'VALUES', 'SET', 'TABLE',
        'DATABASE', 'UNION', 'AND', 'OR', 'NOT', 'LIKE', 'IS', 'NULL', 'INDEX', 'ORDER',
        'BY', 'GROUP', 'LIMIT', 'OFFSET', 'ASC', 'DESC',
    ];

    /**
     * Checks if the given string is a safe SQL identifier.
     *
     * A safe SQL identifier contains only letters, digits, and underscores,
     * and must not match any reserved SQL keyword (e.g., SELECT, INSERT, etc.).
     *
     * @param string $identifier The identifier string to validate.
     * @return bool True if the identifier is considered safe, false otherwise.
     */
    public static function isSafeIdentifier(string $identifier): bool
    {
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $identifier)) {
            return false;
        }

        if (in_array(strtoupper($identifier), self::SQL_RESERVED_WORDS, true)) {
            return false;
        }

        return true;
    }

    /**
     * Asserts that the given string is a safe SQL identifier.
     *
     * Throws an exception if the identifier contains illegal characters
     * or matches a reserved SQL keyword.
     *
     * @param string $identifier The identifier string to validate.
     *
     * @throws \InvalidArgumentException If the identifier is unsafe.
     */
    public static function assertSafeIdentifier(string $identifier): void
    {
        if (!self::isSafeIdentifier($identifier)) {
            throw new \InvalidArgumentException("Unsafe SQL identifier: $identifier");
        }
    }

    /**
     * Validates and formats a default value for use in SQL statements.
     *
     * This method safely transforms a PHP value into a SQL-compatible default value
     * for use in column definitions (`CREATE TABLE`, `ALTER TABLE`, etc.). It ensures
     * the value does not contain any dangerous or unescaped characters such as backticks (`) or semicolons (;).
     *
     * Supported types:
     * - `null` → returns `NULL`
     * - `bool` → returns `'1'` or `'0'`
     * - `int|float` → returns numeric value as string
     * - `string` → returns a safely escaped quoted string (throws if invalid characters are present)
     *
     * @param mixed $value The input value to format as a SQL default.
     * @return string The SQL-safe default value as a string.
     *
     * @throws \InvalidArgumentException If the value is a string with unsafe characters,
     *                                   or if the type is unsupported.
     */
    public static function formatDefaultValue(mixed $value): string
    {
        if ($value === null) {
            return 'NULL';
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_numeric($value)) {
            return "'" . ((string) $value) . "'";
        }

        if (is_string($value)) {
            if (preg_match('/[`;]/', $value)) {
                throw new \InvalidArgumentException("Invalid characters in default value.");
            }

            return "'" . addslashes($value) . "'";
        }

        throw new \InvalidArgumentException("Unsupported default value type.");
    }
}
