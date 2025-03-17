<?php

declare(strict_types=1);

namespace App\Models;

use Nette\Database\Explorer;
use Nette\InvalidArgumentException;

class TranslationManager
{
    public const TABLE_NAME = 'translations';

    private string $currentLang = DEFAULT_LANG;

    /** @var array<string,array<string,string>> */
    private array $translations = [];

    public function __construct(
        private Explorer $db,
        private TranslationLanguage $languageService
    ) {
        $this->currentLang = $this->languageService->getDefaultLang(DEFAULT_LANG);
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

        return $this->translations[$lang][$key] ?? $key;
    }

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

    public function getLanguageService(): TranslationLanguage
    {
        return $this->languageService;
    }
}
