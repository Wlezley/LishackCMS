<?php

declare(strict_types=1);

namespace App\Models\Helpers;

class IntegerHelper
{
    /**
     * Converts a value to an integer or returns null if the value is not numeric.
     *
     * @param mixed $value The value to convert.
     * @return int|null Returns the integer value or null if not numeric.
     */
    public static function toIntOrNull(mixed $value): ?int
    {
        if (is_numeric($value)) {
            return (int) $value;
        }

        return null;
    }
}
