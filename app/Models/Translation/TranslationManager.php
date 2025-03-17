<?php

declare(strict_types=1);

namespace App\Models;

use Nette\Database\Explorer;

class TranslationManager
{
    public const TABLE_NAME = 'translations';

    private string $currentLang = DEFAULT_LANG;

    public function __construct(
        private Explorer $db
    ) {}

    public function setLanguage(string $lang): void
    {
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
}
