<?php

declare(strict_types=1);

namespace App\Models\Translation;

use App\Enum\ErrorCode\TranslatorErrorCode;
use App\Exception\TranslatorException;

class TranslationCache
{
    /** @var array<string, array<string, string>> Cached translations indexed by language and key */
    private array $cache = [];

    public function __construct()
    {
    }

    public function isLanguageLoaded(string $lang): bool
    {
        return array_key_exists($lang, $this->cache);
    }

    public function invalidate(string $lang): void
    {
        unset($this->cache[$lang]);
    }

    public function reset(): void
    {
        $this->cache = [];
    }

    public function hasTranslation(string $languageCode, string $key): bool
    {
        return isset($this->cache[$languageCode][$key]);
    }

    /**
     * @throws TranslatorException
     */
    public function assertTranslationExists(string $languageCode, string $key, ?string $messageTail = null): void
    {
        if (!$this->hasTranslation($languageCode, $key)) {
            throw new TranslatorException(
                message: "Translation (key:'$key', lang:'$languageCode') not found" . ($messageTail ? ", $messageTail" : ''),
                code: TranslatorErrorCode::TranslationNotFound,
            );
        }
    }

    /**
     * @throws TranslatorException
     */
    public function assertTranslationNotExists(string $languageCode, string $key, ?string $messageTail = null): void
    {
        if ($this->hasTranslation($languageCode, $key)) {
            throw new TranslatorException(
                message: "Duplicate translation (key:'$key', lang:'$languageCode') found" . ($messageTail ? ", $messageTail" : ''),
                code: TranslatorErrorCode::DuplicateTranslationFound,
            );
        }
    }

    public function pullTranslation(string $lang, string $key): string
    {
        return $this->cache[$lang][$key] ?? $key;
    }

    public function pushTranslation(string $lang, string $key, string $value): void
    {
        $this->cache[$lang][$key] = $value;
    }

    /**
     * @return array<string,string>
     */
    public function pullLanguage(string $lang): array
    {
        return $this->cache[$lang] ?? [];
    }

    /**
     * @param array<string,string> $data
     */
    public function pushLanguage(string $lang, array $data): void
    {
        $this->cache[$lang] = $data;
    }

    /**
     * @return array<string,array<string,string>>
     */
    public function pullAll(): array
    {
        return $this->cache;
    }

    /**
     * @param array<string,array<string,string>> $data
     */
    public function pushAll(array $data): void
    {
        $this->cache = $data;
    }
}
