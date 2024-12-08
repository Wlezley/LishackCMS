<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\SmsGateException;
use Carbon\Carbon;
use Exception;
use Nette\Database\Explorer;
use Nette\Utils\Random;

// https://www.smsbrana.cz/dokumentace
class SmsGate
{
    public const TABLE_NAME = 'log_sms';
    public const SYSTEM_USER_ID = 0;

    public function __construct(
        private string $apiURL,
        private string $login,
        private string $password,
        private int $sender_id,
        private bool $secured_login,
        private Explorer $db
    ) { }

    public function sendSMS(string $phone_number, string $message, int $user_id = self::SYSTEM_USER_ID): void
    {
        $credentials = [];

        if ($this->secured_login === false) {
            $credentials = $this->getBasicCredentials();
        } else {
            $credentials = $this->getSecuredCredentials();
        }

        $params = array_merge($credentials, [
            'sender_id' => $this->sender_id,
            'action' => 'send_sms',
            'number' => $phone_number,
            'message' => $message,
        ]);

        $query = http_build_query($params, '', '&', PHP_QUERY_RFC3986);
        $request = $this->apiURL . '?' . $query;
        $response = false;

        try {
            $response = file_get_contents($request); // Todo: curl
        } catch (Exception $e) {
            throw $e;
        }

        if ($response === false) {
            throw new Exception('Error processing request', 1);
        }

        $this->logSMS($phone_number, $message, $response, $user_id);

        $responseData = json_decode(json_encode(simplexml_load_string($response)), TRUE);
        $errorCode = $responseData['err'];

        if (DEBUG === true) {
            bdump([
                'query' => $this->secured_login ? $query : 'HIDDEN',
                'request' => $this->secured_login ? $request : 'HIDDEN',
                'response' => $responseData,
            ], 'SMS GATE');
        }

        if (!isset($errorCode) || $errorCode != 0) {
            throw new SmsGateException((int)$errorCode);
        }
    }

    /** @return array<string, string> $param */
    private function getBasicCredentials(): array
    {
        return [
            'login' => $this->login,
            'password' => $this->password,
        ];
    }

    /** @return array<string, string> $param */
    private function getSecuredCredentials(): array
    {
        $time = Carbon::now()->addMinute()->format('Ymd\THis');
        $salt = Random::generate(20, 'a-zA-Z');

        return [
            'login' => $this->login,
            'time' => $time,
            'salt' => $salt,
            'auth' => md5($this->password . $time . $salt),
        ];
    }

    private function logSMS(string $phone_number, string $message, string|false $response, int $user_id = self::SYSTEM_USER_ID): void
    {
        $this->db->table(self::TABLE_NAME)->insert([
            'user_id' => $user_id,
            'phone_number' => $phone_number,
            'message' => $message,
            'response' => $response // Todo: using just error code (int)
        ]);
    }
}
