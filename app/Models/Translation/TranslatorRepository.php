<?php

declare(strict_types=1);

namespace App\Models\Translation;

use App\Exception\TranslatorException;
use Nette\Database\Explorer;
use Nette\Database\Table\ActiveRow;

final class TranslatorRepository
{
    public const string TABLE_NAME = 'translations';

    public function __construct(
        private readonly Explorer $db,
        private readonly TranslatorRepositoryCache $cache,
    ) {
    }

    private function updateCache(string $lang, bool $force = false): void
    {
        if ($this->cache->isLanguageLoaded($lang) && !$force) {
            return;
        }

        $data = $this->db->table(self::TABLE_NAME)
            ->where('lang', $lang)
            ->fetchPairs('key', 'text');

        $this->cache->pushLanguage($lang, $data);
    }

    /**
     * Retrieves a translation text for a given language and key.
     *
     * @param string $lang The language code.
     * @param string $key The translation key.
     */
    public function get(string $lang, string $key): string
    {
        $this->updateCache($lang);

        if ($this->cache->hasTranslation($lang, $key)) {
            return $this->cache->pullTranslation($lang, $key);
        }

        return $key;
    }

    /**
     * Checks if a translation entry exists for a given language and key in the cache.
     *
     * @param string $lang The language code.
     * @param string $key The translation key.
     */
    public function exists(string $lang, string $key): bool
    {
        $this->updateCache($lang);

        return $this->cache->hasTranslation($lang, $key);
    }

    /**
     * Checks if a translation entry exists in the database.
     *
     * @param string $key The translation key.
     * @param string|null $lang The language code (if null, checks in all languages).
     */
    public function existsInDB(string $key, ?string $lang = null): bool
    {
        $query = $this->db->table(self::TABLE_NAME)
            ->where('key', $key);

        if ($lang !== null) {
            $query->where('lang', $lang);
        }

        return $query->fetch() !== null;
    }

    /**
     * Adds a new translation entry.
     *
     * @param string $key The translation key.
     * @param string $lang The language code.
     * @param string $text The translated text.
     * @throws TranslatorException If the key already exists for the given language.
     */
    public function add(string $key, string $lang, string $text): void
    {
        $this->updateCache($lang);

        if ($this->cache->hasTranslation($lang, $key) === true) {
            throw new TranslatorException("Duplicate translation (key:'$key', lang:'$lang') found, entry cannot be added", 1);
        }

        $this->db->table(self::TABLE_NAME)->insert([
            'key' => $key,
            'lang' => $lang,
            'text' => $text,
        ]);

        $this->cache->pushTranslation($lang, $key, $text);
    }

    /**
     * Updates an existing translation entry.
     *
     * @param string $key The translation key.
     * @param string $lang The language code.
     * @param string $text The updated translated text.
     * @throws TranslatorException If the translation does not exist.
     */
    public function update(string $key, string $lang, string $text): void
    {
        $this->updateCache($lang);

        if ($this->cache->hasTranslation($lang, $key) === false) {
            throw new TranslatorException("Translation (key:'$key', lang:'$lang') not found, entry cannot be updated", 1);
        }

        $this->db->table(self::TABLE_NAME)->where([
            'key' => $key,
            'lang' => $lang,
        ])->update([
            'text' => $text,
        ]);

        $this->cache->pushTranslation($lang, $key, $text);
    }

    /**
     * Renames a translation key.
     *
     * @param string $oldKey The existing key name.
     * @param string $newKey The new key name.
     * @param string $lang The language code.
     * @throws TranslatorException If the old key does not exist or the new key already exists.
     */
    public function changeKey(string $oldKey, string $newKey, string $lang): void
    {
        $this->updateCache($lang);

        if (!$this->cache->hasTranslation($lang, $oldKey)) {
            throw new TranslatorException("Translation (key:'$oldKey', lang:'$lang') not found, key cannot be changed", 1);
        }

        if ($this->cache->hasTranslation($lang, $newKey)) {
            throw new TranslatorException("Duplicate translation (key:'$newKey', lang:'$lang') found, key cannot be changed", 1);
        }

        $this->db->table(self::TABLE_NAME)->where([
            'key' => $oldKey,
            'lang' => $lang,
        ])->update([
            'key' => $newKey,
        ]);

        $this->cache->invalidate($lang);
    }

    /**
     * Deletes a translation entry.
     *
     * @param string $key The translation key.
     * @param string|null $lang The language code (if null, deletes in all languages).
     */
    public function delete(string $key, ?string $lang = null): void
    {
        $query = $this->db->table(self::TABLE_NAME)
            ->where('key', $key);

        if ($lang !== null) {
            $query->where('lang', $lang);
            $this->cache->invalidate($lang);
        } else {
            $this->cache->reset();
        }

        $query->delete(); // TODO: Return number of deleted rows ???
    }

    /**
     * Retrieves a paginated list of translations for a specific language.
     *
     * @param string $lang The language code.
     * @param int<0,max>|null $limit The maximum number of records to return.
     * @param int<0,max>|null $offset The number of records to skip.
     * @param string|null $search Optional search query to filter by key or text.
     * @return array<string,string> Associative array of translation keys and texts.
     */
    public function getList(string $lang, ?int $limit = 50, ?int $offset = 0, ?string $search = null): array
    {
        $query = $this->db->table(TranslatorRepository::TABLE_NAME)
            ->where('lang', $lang)
            ->limit($limit, $offset);

        if ($search !== null) {
            $query->whereOr([
                'key LIKE ?' => "%$search%",
                'text LIKE ?' => "%$search%",
            ]);
        }

        return $query->fetchPairs('key', 'text');
    }

    /**
     * Returns the number of translations for a given language.
     *
     * @param string $lang The language code.
     * @param string|null $search Optional search query to filter by key or text.
     * @return int The count of matching translations.
     * @todo Move to Repository
     */
    public function getCount(string $lang, ?string $search = null): int
    {
        $query = $this->db->table(TranslatorRepository::TABLE_NAME)
            ->where('lang', $lang);

        if ($search !== null) {
            $query->whereOr([
                'key LIKE ?' => "%$search%",
                'text LIKE ?' => "%$search%",
            ]);
        }

        return $query->count('*');
    }

    /**
     * Retrieves translations of a specific key in all available languages.
     *
     * @param string $key The translation key.
     * @return array<string,string> Associative array where keys are language codes and values are translations.
     * @todo Move to Repository
     */
    public function getTextListByKey(string $key): array
    {
        return $this->db->table(TranslatorRepository::TABLE_NAME)
            ->where('key', $key)
            ->fetchPairs('lang', 'text');
    }

    /**
     * Retrieves translation pairs for a given target language and default language.
     *
     * @param string $targetLanguage The target language code.
     * @param string $defaultLanguage The default language code.
     * @return ActiveRow[] An array of translation pairs with keys, languages, and texts.
     */
    public function getTranslationPairs(string $targetLanguage, string $defaultLanguage): array
    {
        return $this->db->table(TranslatorRepository::TABLE_NAME)
            ->select('key, lang, text')
            ->where('lang = ? OR lang = ?', $defaultLanguage, $targetLanguage)
            // ->order('key, lang') // TODO: Add sort order option
            ->fetchAll();
    }
}
