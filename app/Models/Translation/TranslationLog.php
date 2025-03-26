<?php

declare(strict_types=1);

namespace App\Models;

use Nette\Database\Explorer;
use Nette\Http\Request as HttpRequest;
use Nette\Utils\Json;

class TranslationLog
{
    public const TABLE_NAME = 'translations_log';

    public const TYPE_MISSING_KEY = 'key';
    public const TYPE_MISSING_ARGUMENTS = 'arg';
    public const TYPE_UNKNOWN = 'unk';

    public function __construct(
        private Explorer $db,
        private HttpRequest $httpRequest
    ) {}

    public function logMissingKey(string $key, string $lang, ?string $url = null): void
    {
        $url = $url ?? $this->httpRequest->getUrl()->getAbsoluteUrl();

        $this->log($key, $lang, self::TYPE_MISSING_KEY, $url);
    }

    /** @param array<mixed> $values */
    public function logMissingArguments(string $key, string $lang, array $values, ?string $error = null, ?string $url = null): void
    {
        $message = Json::encode([
            'url' => $url ?? $this->httpRequest->getUrl()->getAbsoluteUrl(),
            'error' => $error ?? 'unknown',
            'values' => $values,
        ]);

        $this->log($key, $lang, self::TYPE_MISSING_ARGUMENTS, $message);
    }

    private function log(string $key, string $lang, string $type, string $message): void
    {
        $exists = $this->db->table(self::TABLE_NAME)
            ->where([
                'key' => $key,
                'lang' => $lang,
                'type' => $type,
                'message' => $message
            ])->count('*');

        if ($exists === 0) {
            $this->db->table(self::TABLE_NAME)->insert([
                'key' => $key,
                'lang' => $lang,
                'type' => $type,
                'message' => $message
            ]);
        }
    }
}
