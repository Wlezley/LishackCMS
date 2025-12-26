<?php

declare(strict_types=1);

namespace App\Models\Translation;

use App\Exception\TranslatorException;
use App\Models\Config\ConfigManager;
use ValueError;

class Translator
{
    public const string TABLE_NAME = 'translations';

    /**
     * @param ConfigManager $configManager Manages configuration settings, including the default language.
     * @param TranslatorLog $log Handles logging of missing or problematic translations.
     */
    public function __construct(
        private readonly LanguageService $languageService,
        private readonly ConfigManager $configManager,
        private readonly TranslatorLog $log, // TODO: Refactor
        private readonly TranslatorRepository $repository,
        private readonly TranslatorEditor $translationEditor,
    ) {
    }

    /**
     * Retrieves a translation for a given key and language.
     *
     * @param string $key The translation key.
     * @param string|null $lang The language code (defaults to the current language).
     * @return string The translated text or if not found, returns `$key` as fallback if `$keyAsFallback` is true.
     */
    public function translate(string $key, ?string $lang = null): string
    {
        $lang = $lang ?? $this->languageService->getCurrentLanguage();

        if ($this->repository->exists($lang, $key) === false) {
            if ($this->configManager->get('LOG_TRANSLATION_FALLBACK') == 1) {
                $this->log->logMissingKey($key, $lang);
            }

            return $key;
        }

        return $this->repository->get($lang, $key);
    }

    /**
     * Retrieves a formatted translation for a given key and language.
     * Uses `vsprintf()` to format the translation with the provided values.
     *
     * - If the translation key is not found, the function returns the key itself.
     * - If no values are provided, the untranslated format string is returned.
     * - If the number of values does not match the expected placeholders in the translation,
     *   a `ValueError` is caught, and the key itself is returned instead.
     *
     * @param string $key The translation key.
     * @param string|null $lang The language code (defaults to the current language).
     * @param array<mixed> $values Values to be formatted into the translation string.
     * @return string The formatted translated text. If translation is missing or formatting fails, returns the key as a fallback.
     */
    public function translateFormat(string $key, ?string $lang, array $values): string // TODO: Switch order of $values and $lang params
    {
        $lang = $lang ?? $this->languageService->getCurrentLanguage();

        $format = $this->translate($key, $lang);

        try {
            return vsprintf($format, $values);
        } catch (ValueError $e) {
            if ($this->configManager->get('LOG_TRANSLATION_FALLBACK') == 1) {
                $this->log->logMissingArguments($key, $lang, $values, $e->getMessage());
            }
        }

        return $key;
    }

    public function add(string $key, string $lang, string $text): void
    {
        $this->repository->add($key, $lang, $text);
    }

    public function update(string $key, string $lang, string $text): void
    {
        $this->repository->update($key, $lang, $text);
    }

    public function delete(string $key, ?string $lang = null): void
    {
        $this->repository->delete($key, $lang);
    }

    public function changeKey(string $oldKey, string $newKey, string $lang): void
    {
        $this->repository->changeKey($oldKey, $newKey, $lang);
    }

    public function existsInDB(string $key, ?string $lang = null): bool
    {
        return $this->repository->existsInDB($key, $lang);
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
        return $this->repository->getList($lang, $limit, $offset, $search);
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
        return $this->repository->getCount($lang, $search);
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
        return $this->repository->getTextListByKey($key);
    }

    /**
     * Saves multiple translations in a batch.
     *
     * Automatically inserts, updates, or deletes translations based on provided data.
     *
     * @param array<string,array<string,string>> $translations
     *        Nested array where first-level keys are translation keys,
     *        second-level keys are language codes, and values are translated texts.
     *
     * @throws TranslatorException
     * @todo Optimize. see: https://doc.nette.org/en/database/explorer#toc-selection-insert
     */
    public function saveTranslations(array $translations): void
    {
        $this->translationEditor->saveTranslations($translations);
    }

    /**
     * Retrieves translations for a specific language, including defaults.
     *
     * @param string $targetLang The language code to retrieve translations for.
     * @return array<string,array<string,string>> Associative array where:
     *         - First-level keys are translation keys.
     *         - Second-level keys are language codes (or 'default' for fallback).
     *         - Values are translated texts.
     * @todo Move to Repository (partially done in getTranslations())
     */
    public function getTranslations(string $targetLang): array
    {
        return $this->translationEditor->getTranslations($targetLang);
    }
}
