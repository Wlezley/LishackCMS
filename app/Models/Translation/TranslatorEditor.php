<?php

declare(strict_types=1);

namespace App\Models\Translation;

use App\Exception\TranslatorException;

final readonly class TranslatorEditor
{
    public function __construct(
        private TranslatorRepository $repository,
        private LanguageService $languageService,
    ) {
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
        $defaultLanguage = $this->languageService->getDefaultLanguage();

        foreach ($translations as $key => $texts) {
            foreach ($texts as $lang => $text) {
                if ($lang == 'default') {
                    $lang = $defaultLanguage;
                }

                if ($this->repository->exists($lang, $key)) {
                    if (!empty($text)) {
                        $this->repository->update($key, $lang, $text); // UPDATE
                    } else {
                        $this->repository->delete($key, $lang); // DELETE
                    }
                } else {
                    if (!empty($text)) {
                        $this->repository->add($key, $lang, $text); // INSERT
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
        $defaultLanguage = $this->languageService->getDefaultLanguage();

        $translations = [];
        foreach ($this->repository->getTranslationPairs($targetLanguage, $defaultLanguage) as $row) {
            $lang = $row['lang'] == $defaultLanguage ? 'default' : $row['lang'];
            $translations[$row['key']][$lang] = $row['text'];
        }

        return $translations;
    }
}
