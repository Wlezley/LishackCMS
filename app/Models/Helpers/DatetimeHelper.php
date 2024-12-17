<?php

declare(strict_types=1);

namespace App\Models\Helpers;

class DatetimeHelper
{
    /**
     * Validates if the given string matches the MySQL DATETIME format.
     *
     * This method checks if the input string adheres to the MySQL DATETIME format (YYYY-MM-DD HH:MM:SS).
     * It performs a two-step validation:
     * 1. A regular expression ensures the basic structure of the string is correct.
     * 2. A `DateTime` object is created from the string to confirm it represents a valid date and time.
     *
     * @param string $datetime The input string to validate.
     * @return bool True if the input is a valid MySQL DATETIME, false otherwise.
     */
    public static function isValidMySQLDatetime(string $datetime): bool
    {
        // Use a regex pattern to roughly match DATETIME format
        if (!is_string($datetime) || !preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $datetime)) {
            return false;
        }

        // Check if the format matches MySQL DATETIME (YYYY-MM-DD HH:MM:SS)
        $dateTimeObject = \DateTime::createFromFormat('Y-m-d H:i:s', $datetime);

        // Ensure that the object is valid and the input matches exactly
        return $dateTimeObject !== false && $dateTimeObject->format('Y-m-d H:i:s') === $datetime;
    }

    /**
     * Asserts that the given value matches the MySQL DATETIME format.
     *
     * This method validates the input string to ensure it adheres to the MySQL DATETIME format
     * (YYYY-MM-DD HH:MM:SS). If the input is invalid, it throws an exception with a descriptive error message.
     *
     * @param string $value The input string to validate as a MySQL DATETIME.
     * @param string $label A custom label for the variable used in the error message. Defaults to 'variable'.
     *
     * @throws \InvalidArgumentException If the input is not a valid MySQL DATETIME.
     */
    public static function assertMySQLDatetime(string $value, string $label = 'variable'): void
    {
        if (!self::isValidMySQLDatetime($value)) {
            throw new \InvalidArgumentException("The $label must be in a valid MySQL DATETIME (YYYY-MM-DD HH:MM:SS) format.");
        }
    }
}
