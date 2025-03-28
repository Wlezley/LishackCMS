<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Helpers\ArrayHelper;
use Nette\Utils\Validators;

class ConfigValidator
{
    public const COLUMNS = [
        'key',
        'caregory',
        'value'
    ];

    /**
     * Builds a structured array of config data with default values.
     *
     * @param string $key The key of the config item
     * @param string|null $category
     * @param string|null $value
     *
     * @return array<string,string|int|null> An associative array containing config data
     */
    public static function buildData(string $key, ?string $category = null, ?string $value = null): array
    {
        return [
            'key' => $key,
            'category' => $category ?? '',
            'value' => $value ?? '',
        ];
    }

    /**
     * Prepares config data by normalizing and ensuring all required fields have valid values.
     *
     * @param array<string,string|int|null> $data The raw config data array.
     * @return array<string,string|int|null> The prepared config data array.
     */
    public static function prepareData(array $data): array
    {
        return [
            'key' => $data['key'],
            'category' => $data['category'] ?? null,
            'value' => $data['value'] ?? null,
        ];
    }

    /**
     * Validates config data against expected formats and constraints.
     *
     * @param array<string,string|int|null> $data The data to validate
     * @throws \InvalidArgumentException If any validation rule fails
     */
    public static function validateData(array $data): void
    {
        ArrayHelper::assertExtraKeys(self::COLUMNS, $data, 'ConfigData');

        if (isset($data['key'])) {
            Validators::assert($data['key'], 'string:1..50', 'Key');
            // StringHelper::assertSlug($data['key'], 'Key');
        }
        if (isset($data['category'])) {
            Validators::assert($data['category'], 'string:1..50', 'Category');
            // StringHelper::assertSlug($data['category'], 'Category');
        }
        if (isset($data['value'])) {
            Validators::assert($data['value'], 'string', 'Value');
        }
    }
}
