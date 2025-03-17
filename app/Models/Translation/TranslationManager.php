<?php

declare(strict_types=1);

namespace App\Models;

use Nette\Database\Explorer;

class TranslationManager
{
    public const TABLE_NAME = 'translations';
    public const LANG_TABLE_NAME = 'lang';

    private string $currentLang = DEFAULT_LANG;

    public function __construct(
        private Explorer $db
    ) {}

    /** @throws \InvalidArgumentException */
    public function setCurrentLanguage(string $lang): void
    {
        if (!$this->getLanguageData($lang)) {
            throw new \InvalidArgumentException("Language with code '$lang' is not defined.");
        }

        $this->currentLang = $lang;
    }

    public function get(string $key, ?string $lang = null): string
    {
        return $this->db->table(self::TABLE_NAME)
            ->where('key', $key)
            ->where('lang', $lang ?? $this->currentLang)
            ->fetch()->text ?? $key;
    }

    public function add(string $key, string $lang, string $text): void
    {
        $this->db->table(self::TABLE_NAME)->insert([
            'key' => $key,
            'lang' => $lang,
            'text' => $text
        ]);
    }

    public function save(string $key, string $lang, string $text): void
    {
        $this->db->table(self::TABLE_NAME)
            ->where([
                'key' => $key,
                'lang' => $lang
            ])->update([
                'text' => $text
        ]);
    }

    /** @return array<T|mixed> */
    public function getList(string $lang, int $limit = 50, int $offset = 0, ?string $search = null): array
    {
        $query = $this->db->table(self::TABLE_NAME)
            ->where('lang', $lang)
            ->limit($limit, $offset);

        if ($search !== null) {
            $query->where('key LIKE ?', "%$search%");
        }

        return $query->fetchPairs('key', 'text');
    }

    public function getCount(string $lang, ?string $search = null): int
    {
        $query = $this->db->table(self::TABLE_NAME)
            ->where('lang', $lang);

        if ($search !== null) {
            $query->where('key LIKE ?', "%$search%");
        }

        return $query->count('*');
    }

    /** @return null|array<T|mixed> */
    public function getLanguageData(string $lang): ?array
    {
        $row = $this->db->table(self::LANG_TABLE_NAME)
            ->where('lang', $lang)
            ->fetch();

        return $row ? $row->toArray() : null;
    }

    /** @return array<T|mixed> */
    public function getLanguageList(bool $enabledOnly = true): array
    {
        $query = $this->db->table(self::LANG_TABLE_NAME)
            ->select('*');

        if ($enabledOnly) {
            $query->where('enabled', 1);
        }

        return $query->fetchAll();
    }

    /** @return array<T|mixed> */
    public function getLanguageNames(bool $enabledOnly = true): array
    {
        $query = $this->db->table(self::LANG_TABLE_NAME)
            ->select('lang, name');

        if ($enabledOnly) {
            $query->where('enabled', 1);
        }

        return $query->fetchPairs('lang', 'name');
    }

    public function getDefaultLang(?string $fallback = null): ?string
    {
        $row = $this->db->table(self::LANG_TABLE_NAME)
            ->where('default', 1)
            ->fetch();

        return $row ? $row['lang'] : $fallback;
    }
}
