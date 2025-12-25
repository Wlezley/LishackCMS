<?php

declare(strict_types=1);

namespace App\Models;

use App\Exception\SmsGateException;
use Carbon\Carbon;
use Nette\Database\Explorer;
use Nette\Utils\Random;
use SimpleXMLElement;
use Webmozart\Assert\Assert;

/**
 * See: https://www.smsbrana.cz/dokumentace
 */
class SmsGate
{
    public const TABLE_NAME = 'log_sms';
    public const SYSTEM_USER_ID = 0;

    public function __construct(
        private string $apiURL,
        private string $login,
        private string $password,
        private int $senderID,
        private bool $securedLogin,
        private Explorer $db
    ) {
    }

    public function sendSMS(string $phoneNumber, string $message, int $userID = self::SYSTEM_USER_ID): void
    {
        $credentials = [];

        if ($this->securedLogin === false) {
            $credentials = $this->getBasicCredentials();
        } else {
            $credentials = $this->getSecuredCredentials();
        }

        $params = array_merge($credentials, [
            'sender_id' => $this->senderID,
            'action' => 'send_sms',
            'number' => $phoneNumber,
            'message' => $message,
        ]);

        $query = http_build_query($params, '', '&', PHP_QUERY_RFC3986);
        $request = $this->apiURL . '?' . $query;

        $response = file_get_contents($request); // Todo: curl
        Assert::string($response, 'Error processing request');

        $xml = simplexml_load_string($response);
        Assert::isInstanceOf($xml, SimpleXMLElement::class, 'Error processing request XML');

        $encodedResponse = json_encode($xml);
        Assert::string($encodedResponse, 'Error processing request (encoding)');

        $responseData = json_decode($encodedResponse, true);
        Assert::isArray($responseData, 'Error processing request (decoding)');
        Assert::keyExists($responseData, 'err', 'Error processing request (missing err key)');

        $errorCode = $responseData['err'];
        $this->logSMS($phoneNumber, $message, $errorCode, $userID);

        if (DEBUG === true) {
            bdump([
                'query' => $this->securedLogin ? $query : 'HIDDEN',
                'request' => $this->securedLogin ? $request : 'HIDDEN',
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

    private function logSMS(string $phoneNumber, string $message, ?int $errorCode, int $userID = self::SYSTEM_USER_ID): void
    {
        $this->db->table(self::TABLE_NAME)->insert([
            'user_id' => $userID,
            'phone_number' => $phoneNumber,
            'message' => $message,
            'error_code' => $errorCode,
        ]);
    }
}
