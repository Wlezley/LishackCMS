<?php

declare(strict_types=1);

namespace App\Exception;

use App\Enum\ErrorCode\TranslatorErrorCode;
use Throwable;

class TranslatorException extends \Exception
{
    public function __construct(string $message = '', TranslatorErrorCode|int $code = 0, ?Throwable $previous = null)
    {
        if (!is_int($code)) {
            $code = $code->value;
        }

        parent::__construct($message, $code, $previous);
    }
}
