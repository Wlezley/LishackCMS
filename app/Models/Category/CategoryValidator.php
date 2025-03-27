<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Helpers\ArrayHelper;
use App\Models\Helpers\StringHelper;
use Nette\Utils\Validators;

class CategoryValidator
{
    public const COLUMNS = [
        'id',
        'parent_id',
        'position',
        'name',
        'name_url',
        'title',
        'description',
        'body',
        'hidden'
    ];

    /**
     * Builds a structured array of category data with default values.
     *
     * @param string $name The name of the category item
     * @param int $parentID The parent category item's ID (default: 1)
     * @param int $position The position in the category order (default: 0)
     * @param string|null $nameURL The URL-friendly name (default: generated from $name)
     * @param string|null $title The title of the category item
     * @param string|null $description A brief description of the category item
     * @param string|null $body The content/body of the category item
     * @param bool $hidden Whether the category item is hidden (default: false)
     *
     * @return array<string,string|int|null> An associative array containing category data
     */
    public static function buildData(string $name, int $parentID = 1, int $position = 0, ?string $nameURL = null, ?string $title = null, ?string $description = null, ?string $body = null, bool $hidden = false): array
    {
        return [
            // 'id' => $id, // ID is not included in the built data
            'parent_id' => $parentID,
            'position' => $position,
            'name' => $name,
            'name_url' => $nameURL ?? StringHelper::webalize($name),
            'title' => $title,
            'description' => $description,
            'body' => $body,
            'hidden' => $hidden ? '1' : '0',
        ];
    }

    /**
     * Prepares category data by normalizing and ensuring all required fields have valid values.
     *
     * @param array<string,string|int|null> $data The raw category data array.
     * @return array<string,string|int|null> The prepared category data array.
     */
    public static function prepareData(array $data): array
    {
        $name = $data['name'] ?? '';
        $nameURL = $data['name_url'] ?? (!empty($name) ? StringHelper::webalize($name) : '');

        return [
            // 'id' => $data['id'] ?? null, // ID is not included in the prepared data
            'parent_id' => $data['parent_id'] ?? 1,
            'position' => $data['position'] ?? 0,
            'name' => $name,
            'name_url' => $nameURL,
            'title' => $data['title'] ?? null,
            'description' => $data['description'] ?? null,
            'body' => $data['body'] ?? null,
            'hidden' => in_array($data['hidden'], ['0', '1'], true) ? $data['hidden'] : '0',
        ];
    }

    /**
     * Validates category data against expected formats and constraints.
     *
     * @param array<string,string|int|null> $data The data to validate
     * @throws \InvalidArgumentException If any validation rule fails
     */
    public static function validateData(array $data): void
    {
        ArrayHelper::assertExtraKeys(self::COLUMNS, $data, 'CategoryData');

        if (isset($data['id'])) {
            Validators::assert($data['id'], 'numericint', 'ID');
        }
        if (isset($data['parent_id'])) {
            Validators::assert($data['parent_id'], 'numericint', 'Parent ID');
        }
        if (isset($data['position'])) {
            Validators::assert($data['position'], 'numericint', 'Position');
        }
        if (isset($data['name'])) {
            Validators::assert($data['name'], 'string:1..255', 'Name');
        }
        if (isset($data['name_url'])) {
            Validators::assert($data['name_url'], 'string:1..255', 'Name URL');
            StringHelper::assertWebalized($data['name_url']);
        }
        if (isset($data['title'])) {
            Validators::assert($data['title'], 'string:1..255', 'Title');
        }
        if (isset($data['description'])) {
            Validators::assert($data['description'], 'string', 'Description');
        }
        if (isset($data['body'])) {
            Validators::assert($data['body'], 'string', 'Body');
        }
        if (isset($data['hidden']) && !in_array($data['hidden'], ['0', '1'], true)) {
            throw new \InvalidArgumentException('Hidden must be either "0" or "1".');
        }
    }
}
