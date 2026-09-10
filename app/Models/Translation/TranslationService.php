<?php

declare(strict_types=1);

namespace App\Models\Translation;

use App\Entity\Translation\Translation;
use App\Entity\Translation\TranslationRepositoryInterface;
use App\Exception\TranslatorException;
use App\Models\Config\ConfigManager;
use ValueError;

readonly class TranslationService
{
    public function __construct(
        private TranslationCache $cache,
        private LanguageService $languageService,
        private ConfigManager $configManager,
        private TranslatorLog $translatorLog,
        private TranslationRepositoryInterface $translationRepository,
    ) {
    }

    /**
     * Retrieves a translation for a given key and language.
     *
     * @param string $key The translation key.
     * @param string|null $languageCode The language code (defaults to the current language).
     * @return string The translated text or if not found, returns `$key` as fallback if `$keyAsFallback` is true.
     * @throws TranslatorException If language is not found.
     */
    public function translate(string $key, ?string $languageCode = null): string
    {
        if ($languageCode === null) {
            $language = $this->languageService->getCurrentLanguage();
        } else {
            $language = $this->languageService->getLanguage($languageCode);
        }

        if ($this->translationRepository->existsByKeyAndLanguage($key, $language) === false) {
            if ($this->configManager->get('LOG_TRANSLATION_FALLBACK') == 1) {
                $this->translatorLog->logMissingKey($key, $language->getCode()); // TODO: Use Language Entity
            }

            return $key;
        }

        // TODO: Idea... Use TranslationRepositoryInterface as cache system with static ???
        return $this->get($language->getCode(), $key);
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
     * @param array<mixed> $values Values to be formatted into the translation string.
     * @param string|null $languageCode The language code (defaults to the current language).
     * @return string The formatted translated text. If translation is missing or formatting fails, returns the key as a fallback.
     */
    public function translateFormat(string $key, array $values, ?string $languageCode = null): string
    {
        if ($languageCode === null) {
            $language = $this->languageService->getCurrentLanguage();
        } else {
            $language = $this->languageService->getLanguage($languageCode);
        }

        $format = $this->translate($key, $language->getCode());

        try {
            return vsprintf($format, $values);
        } catch (ValueError $e) {
            if ($this->configManager->get('LOG_TRANSLATION_FALLBACK') == 1) {
                $this->translatorLog->logMissingArguments(
                    key: $key,
                    lang: $language->getCode(), // TODO: Use Language Entity
                    values: $values,
                    error: $e->getMessage(),
                );
            }
        }

        return $key;
    }

    /**
     * Checks if a translation entry exists for a given language and key in the cache.
     *
     * @param string $languageCode The language code.
     * @param string $key The translation key.
     */
    public function exists(string $languageCode, string $key): bool
    {
        $this->updateCache($languageCode);

        return $this->cache->hasTranslation($languageCode, $key);
    }

    /**
     * Adds a new translation entry.
     *
     * @param string $key The translation key.
     * @param string $languageCode The language code.
     * @param string $text The translated text.
     * @throws TranslatorException
     */
    public function add(string $key, string $languageCode, string $text): void
    {
        $language = $this->languageService->getLanguage($languageCode);

        $this->updateCache($languageCode);

        $this->cache->assertTranslationNotExists($languageCode, $key, 'entry cannot be added');

        $translation = new Translation(
            translationKey: $key,
            language: $language,
            text: $text
        );

        $this->translationRepository->save($translation);

        $this->cache->pushTranslation($languageCode, $key, $text);
    }

    /**
     * Updates an existing translation entry.
     *
     * @param string $key The translation key.
     * @param string $languageCode The language code.
     * @param string $text The updated translated text.
     * @throws TranslatorException
     */
    public function update(string $key, string $languageCode, string $text): void
    {
        $language = $this->languageService->getLanguage($languageCode);

        $this->updateCache($languageCode);

        $this->cache->assertTranslationExists($languageCode, $key, 'entry cannot be updated');

        $translation = $this->translationRepository->findOneByKeyAndLanguage(
            $key,
            $language,
        );

        if ($translation !== null) {
            $translation->setText($text);
            $this->translationRepository->save($translation);
        }

        $this->cache->pushTranslation($languageCode, $key, $text);
    }

    /**
     * Deletes a translation entry.
     *
     * @param string $translationKey The translation key.
     * @param string|null $languageCode The language code (if null, deletes in all languages).
     * @throws TranslatorException
     */
    public function delete(string $translationKey, ?string $languageCode = null): void
    {
        $criteria = ['translationKey' => $translationKey];
        if ($languageCode !== null) {
            $criteria['language'] = $this->languageService->getLanguage($languageCode);
            $this->cache->invalidate($languageCode);
        } else {
            $this->cache->reset();
        }

        foreach ($this->translationRepository->findBy($criteria) as $translation) {
            $this->translationRepository->remove($translation);
        }

        $this->translationRepository->flush();
    }

    /**
     * Renames a translation key.
     *
     * @param string $oldKey The existing key name.
     * @param string $newKey The new key name.
     * @param string $languageCode The language code.
     * @throws TranslatorException
     */
    public function changeKey(string $oldKey, string $newKey, string $languageCode): void
    {
        $language = $this->languageService->getLanguage($languageCode);

        $this->updateCache($languageCode);

        $this->cache->assertTranslationExists($languageCode, $oldKey, 'key cannot be changed');
        $this->cache->assertTranslationNotExists($languageCode, $newKey, 'key cannot be changed');

        $translation = $this->translationRepository->findOneByKeyAndLanguage($oldKey, $language);
        if ($translation) {
            $translation->setTranslationKey($newKey);
            $this->translationRepository->save($translation);
        }

        $this->cache->invalidate($languageCode);
    }

    public function keyExists(string $translationKey): bool
    {
        return $this->translationRepository->exists(['translationKey' => $translationKey]);
    }

    /**
     * Retrieves a paginated list of translations for a specific language.
     *
     * @param string $languageCode The language code.
     * @param string|null $search Optional search query to filter by key or text.
     * @param int<0,max>|null $limit The maximum number of records to return.
     * @param int<0,max>|null $offset The number of records to skip.
     * @return Translation[] An array of Translation entities.
     */
    public function getList(string $languageCode, ?string $search = null, ?int $limit = 50, ?int $offset = 0): array
    {
        return $this->translationRepository->findBySearch($languageCode, $search, $limit, $offset);
    }

    /**
     * Returns the number of translations for a given language.
     *
     * @param string $languageCode The language code.
     * @param string|null $search Optional search query to filter by key or text.
     * @return int The count of matching translations.
     */
    public function getCount(string $languageCode, ?string $search = null): int
    {
        return $this->translationRepository->countBySearch($languageCode, $search);
    }

    /**
     * Retrieves translations of a specific key in all available languages.
     *
     * @param string $key The translation key.
     * @return array<string,string> Associative array where keys are language codes and values are translations.
     */
    public function getTextListByKey(string $key): array
    {
        $translations = $this->translationRepository->findByKey($key);

        $pairs = [];
        foreach ($translations as $translation) {
            $pairs[$translation->getLanguage()->getCode()] = (string)$translation->getText();
        }

        return $pairs;
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
     */
    public function saveTranslations(array $translations): void
    {
        $defaultLanguage = $this->languageService->getDefaultLanguage();

        foreach ($translations as $key => $texts) {
            foreach ($texts as $languageCode => $text) {
                if ($languageCode == 'default') { // TODO: WTF ???
                    $languageCode = $defaultLanguage->getCode();
                }

                if ($this->exists($languageCode, $key)) {
                    if (!empty($text)) {
                        $this->update($key, $languageCode, $text);
                    } else {
                        $this->delete($key, $languageCode);
                    }
                } else {
                    if (!empty($text)) {
                        $this->add($key, $languageCode, $text);
                    }
                }
            }
        }
    }

    /**
     * Retrieves translations for a specific language, including defaults.
     *
     * @param string $targetLanguage The language code to retrieve translations for.
     * @return array<string,array<string,string>> Associative array where:
     *         - First-level keys are translation keys.
     *         - Second-level keys are language codes (or 'default' for fallback).
     *         - Values are translated texts.
     */
    public function getTranslations(string $targetLanguage): array
    {
        $translations = [];
        foreach ($this->getTranslationPairs($targetLanguage) as $translation) {
            $languageCode = $translation->getLanguage()->isDefault() ? 'default' : $translation->getLanguage()->getCode();
            $translations[$translation->getTranslationKey()][$languageCode] = (string)$translation->getText();
        }

        return $translations;
    }

    /**
     * Retrieves translation pairs for a given target language and default language.
     *
     * @param string $targetLanguage The target language code.
     * @return Translation[] An array of translation entities.
     */
    public function getTranslationPairs(string $targetLanguage): array
    {
        $defaultLanguage = $this->languageService->getDefaultLanguage(); // TODO: Optimize !!!

        return $this->translationRepository->findByLanguages($targetLanguage, $defaultLanguage->getCode());
    }

    /**
     * Retrieves a translation text for a given language and key.
     *
     * @param string $languageCode The language code.
     * @param string $key The translation key.
     */
    public function get(string $languageCode, string $key): string
    {
        $this->updateCache($languageCode); // TODO: Remove ???

        if ($this->cache->hasTranslation($languageCode, $key)) {
            return $this->cache->pullTranslation($languageCode, $key);
        }

        return $key;
    }

    private function updateCache(string $languageCode, bool $forceReload = false): void
    {
        if ($this->cache->isLanguageLoaded($languageCode) && !$forceReload) {
            return;
        }

        $translations = $this->translationRepository->findByLanguageCode($languageCode);

        $data = [];
        foreach ($translations as $translation) {
            $data[$translation->getTranslationKey()] = (string) $translation->getText();
        }

        $this->cache->pushLanguage($languageCode, $data);
    }
}
