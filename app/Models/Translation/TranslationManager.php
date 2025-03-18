<?php

declare(strict_types=1);

namespace App\Models;

use Nette\Database\Explorer;
use Nette\InvalidArgumentException;

class TranslationManager
{
    public const TABLE_NAME = 'translations';

    private string $currentLang;

    /** @var array<string,array<string,string>> */
    private array $translations = [];

    public function __construct(
        private Explorer $db,
        private TranslationLanguage $languageService
    ) {
        $this->currentLang = $this->languageService->getDefaultLang(DEFAULT_LANG);
    }

    public function getLanguageService(): TranslationLanguage
    {
        return $this->languageService;
    }

    /** @throws InvalidArgumentException */
    public function setCurrentLanguage(string $lang): void
    {
        if (!$this->languageService->getLanguage($lang)) {
            throw new InvalidArgumentException("Language with code '$lang' is not defined.");
        }

        $this->currentLang = $lang;
        $this->load($lang);
    }

    public function getCurrentLanguage(): string
    {
        return $this->currentLang;
    }

    private function load(string $lang, bool $reload = false): void
    {
        if ($reload) {
            unset($this->translations[$lang]);
        }

        if (!isset($this->translations[$lang])) {
            $this->translations[$lang] = $this->db->table(self::TABLE_NAME)
                ->where('lang', $lang)
                ->fetchPairs('key', 'text');
        }
    }

    public function reload(string $lang): void
    {
        $this->load($lang, true);
    }

    public function get(string $key, ?string $lang = null): string
    {
        $lang = $lang ?? $this->currentLang;

        if (!isset($this->translations[$lang])) {
            $this->load($lang);
        }

        // TODO: Multiple layers of the fallback: 1.) DEFAULT LANGUAGE VALUE, 2.) ($LANG)_$KEY
        return $this->translations[$lang][$key] ?? $key;
    }

    // ADMIN HANDLERS

    public function add(string $key, string $lang, string $text): void
    {
        $this->db->table(self::TABLE_NAME)->insert([
            'key' => $key,
            'lang' => $lang,
            'text' => $text
        ]);

        if (!isset($this->translations[$lang])) {
            $this->load($lang);
        }

        $this->translations[$lang][$key] = $text;
    }

    public function delete(string $key, ?string $lang = null): void
    {
        $query = $this->db->table(self::TABLE_NAME)
            ->where('key', $key);

        if ($lang !== null) {
            $query->where('lang', $lang);
        }

        $query->delete();
        // TODO: catch errors?
    }

    public function save(string $key, string $lang, string $text): void
    {
        $affectedRows = $this->db->table(self::TABLE_NAME)->where([
            'key' => $key,
            'lang' => $lang
        ])->update([
            'text' => $text
        ]);

        if (!isset($this->translations[$lang])) {
            $this->load($lang);
        }

        if ($affectedRows == 1) {
            $this->translations[$lang][$key] = $text;
        } // TODO: catch errors?
    }

    public function changeKey(string $oldKey, string $newKey, string $lang): void
    {
        $affectedRows = $this->db->table(self::TABLE_NAME)->where([
            'key' => $oldKey,
            'lang' => $lang
        ])->update([
            'key' => $newKey
        ]);

        if (!isset($this->translations[$lang])) {
            $this->load($lang);
        }

        if ($affectedRows == 1) {
            $this->translations[$lang][$newKey] = $this->translations[$lang][$oldKey];
            unset($this->translations[$lang][$oldKey]);
        } // TODO: catch errors?
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

    /** @return array<T|mixed> */
    public function getTextListByKey(string $key): array
    {
        return $this->db->table(self::TABLE_NAME)
            ->where('key', $key)
            ->fetchPairs('lang', 'text');
    }
}
