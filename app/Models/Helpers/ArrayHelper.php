<?php

declare(strict_types=1);

namespace App\Models\Helpers;

use Nette\Database\Table\ActiveRow;

class ArrayHelper
{
    /**
     * Converts an array of `ActiveRow` objects into an associative array.
     *
     * Keys are taken from the 'id' field of each row.
     *
     * @param array<ActiveRow> $result Array of `ActiveRow` objects.
     * @return array<int,array<string,string|int|null>> Associative array of row data.
     */
    public static function resultToArray(array $result): array
    {
        $data = [];
        foreach ($result as $row) {
            $item = $row->toArray();
            $data[$item['id']] = $item;
        }
        return $data;
    }

    /**
     * Finds keys that are missing from the given data.
     *
     * @param array<string> $keys Required keys.
     * @param mixed $data Data to check.
     * @return array<string> Missing keys.
     */
    public static function getMissingKeys(array $keys, mixed $data): array
    {
        return array_keys(array_diff_key(array_flip($keys), $data));
    }

    /**
     * Asserts that all required keys are present in the given data.
     *
     * @param array<string> $keys Required keys.
     * @param mixed $data Data to validate.
     * @param string $label Label for the error message. Defaults to 'given'.
     * @throws \InvalidArgumentException If any keys are missing.
     */
    public static function assertMissingKeys(array $keys, mixed $data, string $label = 'given'): void
    {
        $missingKeys = self::getMissingKeys($keys, $data);
        if (!empty($missingKeys)) {
            throw new \InvalidArgumentException("Required keys [" . implode(', ', $missingKeys) . "] are missing in the $label array.");
        }
    }

    /**
     * Finds keys that are not expected in the given data.
     *
     * @param array<string> $keys Allowed keys.
     * @param mixed $data Data to check.
     * @return array<string> Extra keys.
     */
    public static function getExtraKeys(array $keys, mixed $data): array
    {
        return array_keys(array_diff_key($data, array_flip($keys)));
    }

    /**
     * Asserts that no extra keys are present in the given data.
     *
     * @param array<string> $keys Allowed keys.
     * @param mixed $data Data to validate.
     * @param string $label Label for the error message. Defaults to 'given'.
     * @throws \InvalidArgumentException If any extra keys are found.
     */
    public static function assertExtraKeys(array $keys, mixed $data, string $label = 'given'): void
    {
        $extraKeys = self::getExtraKeys($keys, $data);
        if (!empty($extraKeys)) {
            throw new \InvalidArgumentException("There are unknown keys [" . implode(', ', $extraKeys) . "] in the $label array.");
        }
    }
}
