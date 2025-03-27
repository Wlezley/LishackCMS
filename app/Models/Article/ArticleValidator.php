<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Helpers\ArrayHelper;

class ArticleValidator extends BaseModel
{
    public const COLUMNS = [
        'id',
        'title',
        'name_url',
        'content',
        'published',
        'published_at',
        'updated_at',
        'user_id',
        'robots',
        'canonical_url',
        'og_title',
        'og_description',
        'og_image',
        'og_url',
        'og_type',
        'meta_title',
        'meta_description'
    ];

    /**
     * Validates article data against expected formats and constraints.
     *
     * @param array<string,string|int|null> $data The data to validate
     * @throws \InvalidArgumentException If any validation rule fails
     */
    public static function validateData(array $data): void
    {
        ArrayHelper::assertExtraKeys(self::COLUMNS, $data, 'ArticleData');
    }
}
