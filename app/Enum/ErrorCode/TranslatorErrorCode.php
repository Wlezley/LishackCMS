<?php

declare(strict_types=1);

namespace App\Enum\ErrorCode;

enum TranslatorErrorCode: int
{
    case UnknownError = 0;
    case LanguageNotFound = 1;
    case DefaultLanguageNotFound = 2;
    case TranslationNotFound = 3;
    case DuplicateTranslationFound = 4;
}
