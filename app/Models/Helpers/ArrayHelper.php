<?php

declare(strict_types=1);

namespace App\Models\Helpers;

use Nette\Database\Table\ActiveRow;

class ArrayHelper
{
    /**
     * @param array<ActiveRow> $result
     * @return array<int,array<string,string|int|null>>
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
     * @param array<string> $keys
     * @return array<string>
     */
    public static function getMissingKeys(array $keys, mixed $data): array
    {
        return array_keys(array_diff_key(array_flip($keys), $data));
    }

    /** @param array<string> $keys */
    public static function assertMissingKeys(array $keys, mixed $data, string $label = 'given'): void
    {
        $missingKeys = self::getMissingKeys($keys, $data);
        if (!empty($missingKeys)) {
            throw new \InvalidArgumentException("Required keys [" . implode(', ', $missingKeys) . "] are missing in the $label array.");
        }
    }

    /**
     * @param array<string> $keys
     * @return array<string>
     */
    public static function getExtraKeys(array $keys, mixed $data): array
    {
        return array_keys(array_diff_key($data, array_flip($keys)));
    }

    /** @param array<string> $keys */
    public static function assertExtraKeys(array $keys, mixed $data, string $label = 'given'): void
    {
        $extraKeys = self::getExtraKeys($keys, $data);
        if (!empty($extraKeys)) {
            throw new \InvalidArgumentException("There are unknown keys [" . implode(', ', $extraKeys) . "] in the $label array.");
        }
    }
}
