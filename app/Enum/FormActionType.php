<?php

declare(strict_types=1);

namespace App\Enum;

enum FormActionType: string
{
    case Create = 'Create';
    case Edit = 'Edit';

    public function isCreate(): bool
    {
        return $this === self::Create;
    }

    public function isEdit(): bool
    {
        return $this === self::Edit;
    }
}
