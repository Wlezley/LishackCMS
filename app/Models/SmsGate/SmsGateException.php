<?php

declare(strict_types=1);

namespace App\Models;

class SmsGateException extends \Exception
{
    public const ErrorCodes = [
        -1 => 'Duplicate user_id - an SMS with the same mark was already sent in the past',
        0 => 'OK',
        1 => 'Unknown error',
        2 => 'Invalid login',
        3 => 'Invalid hash or password (depending on the login security option)',
        4 => 'Invalid time, greater time difference between servers than the maximum accepted in the SMS Connect service settings',
        5 => 'Invalid IP, see SMS Connect service settings',
        6 => 'Invalid action name',
        7 => 'This salt has already been used today',
        8 => 'A connection to the database was not established',
        9 => 'Insufficient credit',
        10 => 'Invalid SMS recipient number',
        11 => 'Empty text message',
        12 => 'The SMS is longer than the allowed 459 characters',
    ];

    public function __construct(int $code)
    {
        if (!array_key_exists($code, self::ErrorCodes)) {
            $code = 1; // Unknown error (default)
        }

        $message =  self::ErrorCodes[$code];

        parent::__construct($message, $code);
        $this->message = "$message";
        $this->code = $code;
    }
}
