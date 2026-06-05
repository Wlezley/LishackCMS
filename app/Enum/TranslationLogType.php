<?php

declare(strict_types=1);

namespace App\Enum;

enum TranslationLogType: string
{
    case Key = 'key';
    case Arg = 'arg';
    case Unk = 'unk';
}
