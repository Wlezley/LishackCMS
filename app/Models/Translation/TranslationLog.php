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

    /**
     * @param Explorer $db Database explorer instance.
     * @param HttpRequest $httpRequest HTTP request instance for retrieving current URL.
     */
    public function __construct(
        private Explorer $db,
        private HttpRequest $httpRequest
    ) {}

    /**
     * Logs a missing translation key.
     *
     * @param string $key The missing translation key.
     * @param string $lang The language code.
     * @param string|null $url The URL where the missing key was encountered (default: current URL).
     */
    public function logMissingKey(string $key, string $lang, ?string $url = null): void
    {
        $url = $url ?? $this->httpRequest->getUrl()->getAbsoluteUrl();

        $this->log($key, $lang, self::TYPE_MISSING_KEY, $url);
    }

    /**
     * Logs an issue with translation formatting.
     *
     * This occurs when a translation string is used with vsprintf, but the number
     * of provided values does not match the expected placeholders in the translation.
     *
     * @param string $key The translation key.
     * @param string $lang The language code.
     * @param array<mixed> $values The values provided to vsprintf.
     * @param string|null $error Optional error message (e.g., vsprintf() error description).
     * @param string|null $url The URL where the issue occurred (default: current URL).
     */
    public function logMissingArguments(string $key, string $lang, array $values, ?string $error = null, ?string $url = null): void
    {
        $message = Json::encode([
            'url' => $url ?? $this->httpRequest->getUrl()->getAbsoluteUrl(),
            'error' => $error ?? 'unknown',
            'values' => $values,
        ]);

        $this->log($key, $lang, self::TYPE_MISSING_ARGUMENTS, $message);
    }

    /**
     * Internal method to log translation-related issues.
     *
     * Prevents duplicate log entries.
     *
     * @param string $key The translation key.
     * @param string $lang The language code.
     * @param string $type The type of issue (missing key, argument mismatch, etc.).
     * @param string $message Additional information about the issue.
     */
    private function log(string $key, string $lang, string $type, string $message): void
    {
        $exists = $this->db->table(self::TABLE_NAME)
            ->where([
                'key' => $key,
                'lang' => $lang,
                'type' => $type,
                'message' => $message,
            ])->count('*');

        if ($exists === 0) {
            $this->db->table(self::TABLE_NAME)->insert([
                'key' => $key,
                'lang' => $lang,
                'type' => $type,
                'message' => $message,
            ]);
        }
    }
}
