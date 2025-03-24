<?php

declare(strict_types=1);

namespace App\Models;

use Nette\Database\Explorer;
use Nette\InvalidArgumentException;

class TranslationManager
{
    public const TABLE_NAME = 'translations';

    /** @var string Currently selected language */
    private string $currentLang;

    /** @var array<string,array<string,string>> Cached translations indexed by language and key */
    private array $translations = [];

    /**
     * @param Explorer $db Database explorer instance.
     * @param TranslationLanguage $languageService Service for handling language metadata.
     */
    public function __construct(
        private Explorer $db,
        private TranslationLanguage $languageService,
        private ConfigManager $configManager
    ) {
        ;
        $this->currentLang = $this->languageService->getDefaultLang(
            $this->configManager->get('DEFAULT_LANG')
        );
    }

    /**
     * Returns the translation language service.
     *
     * @return TranslationLanguage The language service instance.
     */
    public function getLanguageService(): TranslationLanguage
    {
        return $this->languageService;
    }

    /**
     * Sets the current language for translations.
     *
     * @param string $lang The language code to set.
     * @throws InvalidArgumentException If the language is not found.
     */
    public function setCurrentLanguage(string $lang): void
    {
        if (!$this->languageService->getLanguage($lang)) {
            throw new InvalidArgumentException("Language with code '$lang' is not found.");
        }

        $this->currentLang = $lang;
        $this->load($lang);
    }

    /**
     * Gets the currently set language.
     *
     * @return string The current language code.
     */
    public function getCurrentLanguage(): string
    {
        return $this->currentLang;
    }

    /**
     * Loads translations for a given language.
     *
     * @param string $lang The language code to load translations for.
     */
    private function load(string $lang): void
    {
        if (!isset($this->translations[$lang])) {
            $this->translations[$lang] = $this->db->table(self::TABLE_NAME)
                ->where('lang', $lang)
                ->fetchPairs('key', 'text');
        }
    }

    /**
     * Reloads translations for a given language.
     *
     * @param string $lang The language code to reload.
     */
    public function reload(string $lang): void
    {
        $this->invalidate($lang);
        $this->load($lang);
    }

    /**
     * Clears cached translations for a given language or all languages.
     *
     * @param string|null $lang The language to clear, or null to clear all.
     */
    public function invalidate(?string $lang = null): void
    {
        if ($lang) {
            unset($this->translations[$lang]);
        } else {
            unset($this->translations);
        }
    }

    /**
     * Retrieves a translation for a given key and language.
     *
     * @param string $key The translation key.
     * @param string|null $lang The language code (defaults to the current language).
     * @param bool $keyAsFallback Whether to return the key itself if no translation is found.
     * @return string|null The translated text or if not found, returns `$key` as fallback or null if `$keyAsFallback` is false.
     */
    public function get(string $key, ?string $lang = null, bool $keyAsFallback = true): ?string
    {
        $lang = $lang ?? $this->currentLang;

        $this->load($lang);
        return $this->translations[$lang][$key] ?? ($keyAsFallback ? $key : null);
    }

    /**
     * Adds a new translation entry.
     *
     * @param string $key The translation key.
     * @param string $lang The language code.
     * @param string $text The translated text.
     * @throws TranslationException If the key already exists for the given language.
     */
    public function add(string $key, string $lang, string $text): void
    {
        $this->load($lang);

        if (isset($this->translations[$lang][$key])) {
            throw new TranslationException("Duplicate translation (key:'$key', lang:'$lang') found, entry cannot be added", 1);
        }

        $this->db->table(self::TABLE_NAME)->insert([
            'key' => $key,
            'lang' => $lang,
            'text' => $text
        ]);

        $this->translations[$lang][$key] = $text;
    }

    /**
     * Updates an existing translation entry.
     *
     * @param string $key The translation key.
     * @param string $lang The language code.
     * @param string $text The updated translated text.
     * @throws TranslationException If the translation does not exist.
     */
    public function update(string $key, string $lang, string $text): void
    {
        $this->load($lang);

        if (!isset($this->translations[$lang][$key])) {
            throw new TranslationException("Translation (key:'$key', lang:'$lang') not found, entry cannot be updated", 1);
        }

        $this->db->table(self::TABLE_NAME)->where([
            'key' => $key,
            'lang' => $lang
        ])->update([
            'text' => $text
        ]);

        $this->translations[$lang][$key] = $text;
    }

    /**
     * Renames a translation key.
     *
     * @param string $oldKey The existing key name.
     * @param string $newKey The new key name.
     * @param string $lang The language code.
     * @throws TranslationException If the old key does not exist or the new key already exists.
     */
    public function changeKey(string $oldKey, string $newKey, string $lang): void
    {
        $this->load($lang);

        if (!isset($this->configuration[$oldKey])) {
            throw new TranslationException("Translation (key:'$oldKey', lang:'$lang') not found, key cannot be changed", 1);
        }

        if (isset($this->configuration[$newKey])) {
            throw new TranslationException("Duplicate translation (key:'$newKey', lang:'$lang') found, key cannot be changed", 1);
        }

        $this->db->table(self::TABLE_NAME)->where([
            'key' => $oldKey,
            'lang' => $lang
        ])->update([
            'key' => $newKey
        ]);

        $this->invalidate($lang);
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
        }

        $query->delete();

        $this->invalidate($lang);
    }

    // LISTING METHODS

    /**
     * Retrieves a paginated list of translations for a specific language.
     *
     * @param string $lang The language code.
     * @param int $limit The maximum number of records to return.
     * @param int $offset The number of records to skip.
     * @param string|null $search Optional search query to filter by key or text.
     * @return array<string,string> Associative array of translation keys and texts.
     */
    public function getList(string $lang, int $limit = 50, int $offset = 0, ?string $search = null): array
    {
        $query = $this->db->table(self::TABLE_NAME)
            ->where('lang', $lang)
            ->limit($limit, $offset);

        if ($search !== null) {
            $query->whereOr([
                'key LIKE ?' => "%$search%",
                'text LIKE ?' => "%$search%"
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
     */
    public function getCount(string $lang, ?string $search = null): int
    {
        $query = $this->db->table(self::TABLE_NAME)
            ->where('lang', $lang);

        if ($search !== null) {
            $query->whereOr([
                'key LIKE ?' => "%$search%",
                'text LIKE ?' => "%$search%"
            ]);
        }

        return $query->count('*');
    }

    /**
     * Retrieves translations of a specific key in all available languages.
     *
     * @param string $key The translation key.
     * @return array<string,string> Associative array where keys are language codes and values are translations.
     */
    public function getTextListByKey(string $key): array
    {
        return $this->db->table(self::TABLE_NAME)
            ->where('key', $key)
            ->fetchPairs('lang', 'text');
    }

    // TRANSLATION EDITOR METHODS

    /**
     * Saves multiple translations in batch.
     *
     * Automatically inserts, updates, or deletes translations based on provided data.
     *
     * @param array<string,array<string,string>> $translations
     *        Nested array where first-level keys are translation keys,
     *        second-level keys are language codes, and values are translated texts.
     *
     * @todo Optimize. see: https://doc.nette.org/en/database/explorer#toc-selection-insert
     */
    public function saveTranslations(array $translations): void
    {
        $defaultLang = $this->languageService->getDefaultLang(
            $this->configManager->get('DEFAULT_LANG')
        );

        foreach ($translations as $key => $texts) {
            foreach ($texts as $lang => $text) {
                if ($lang == 'default') {
                    $lang = $defaultLang;
                }

                if ($this->get($key, $lang, false)) {
                    if (!empty($text)) {
                        $this->update($key, $lang, $text); // UPDATE
                    } else {
                        $this->delete($key, $lang); // DELETE
                    }
                } else {
                    if (!empty($text)) {
                        $this->add($key, $lang, $text); // INSERT
                    }
                }
            }
        }
    }

    /**
     * Retrieves translations for a specific language, including defaults.
     *
     * @param string $targetLang The language code to retrieve translations for.
     * @return array<string,array<string,string>> Associative array where:
     *         - First-level keys are translation keys.
     *         - Second-level keys are language codes (or 'default' for fallback).
     *         - Values are translated texts.
     */
    public function getTranslations(string $targetLang): array
    {
        $defaultLang = $this->languageService->getDefaultLang(
            $this->configManager->get('DEFAULT_LANG')
        );

        $translations = [];
        $rows = $this->db->table(self::TABLE_NAME)
            ->select('key, lang, text')
            ->where('lang = ? OR lang = ?', $defaultLang, $targetLang)
            ->order('key, lang')
            ->fetchAll();

        /** @var \Nette\Database\Table\ActiveRow $row */
        foreach ($rows as $row) {
            $lang = $row['lang'] == $defaultLang ? 'default' : $row['lang'];
            $translations[$row['key']][$lang] = $row['text'];
        }

        return $translations;
    }
}
