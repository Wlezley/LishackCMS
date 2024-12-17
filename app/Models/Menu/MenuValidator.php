<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Helpers\ArrayHelper;
use App\Models\Helpers\StringHelper;
use Nette\Utils\Validators;

class MenuValidator
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

    // ##########################################
    // ###             VALIDATION             ###
    // ##########################################

    /** @return array<string,string|int|null> */
    public static function buildData(string $name, int $parentID = 1, int $position = 0, ?string $nameURL = null, ?string $title = null, ?string $description = null, ?string $body = null, bool $hidden = false): array
    {
        return [
            // 'id' => $id,
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
     * @param array<string,string|int|null> $data
     * @return array<string,string|int|null>
     */
    public static function prepareData(array $data): array
    {
        $name = $data['name'] ?? '';
        $nameURL = $data['name_url'] ?? (!empty($name) ? StringHelper::webalize($name) : '');

        return [
            // 'id' => $data['id'] ?? null,
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

    /** @param array<string,string|int|null> $data */
    public static function validateData(array $data): void
    {
        ArrayHelper::assertExtraKeys(self::COLUMNS, $data, 'MenuData');

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
