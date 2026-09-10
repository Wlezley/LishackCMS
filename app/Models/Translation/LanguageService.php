<?php

declare(strict_types=1);

namespace App\Models\Translation;

use App\Entity\Language\Language;
use App\Entity\Language\LanguageRepositoryInterface;
use App\Exception\TranslatorException;
use Webmozart\Assert\Assert;
use Webmozart\Assert\InvalidArgumentException;

class LanguageService
{
    /** @var array<string, Language> */
    private array $languages = [];

    private Language $defaultLanguage;

    private Language $currentLanguage;

    public function __construct(
        private readonly LanguageRepositoryInterface $languageRepository,
    ) {
        $this->load();
        $this->defaultLanguage = $this->languageRepository->findDefault();
        $this->currentLanguage = $this->defaultLanguage; // Current language default to the default language
    }

    private function load(): void
    {
        if (empty($this->languages)) {
            foreach ($this->languageRepository->findAll() as $language) {
                $this->languages[$language->getCode()] = $language;
            }
        }
    }

    public function reload(): void
    {
        $this->invalidate();
        $this->load();
    }

    private function invalidate(): void
    {
        $this->languages = [];
    }

    /**
     * @throws TranslatorException If language is not found.
     */
    public function assertLanguageExists(string $languageCode): void
    {
        try {
            Assert::keyExists($this->languages, $languageCode, "Language '$languageCode' not found.");
        } catch (InvalidArgumentException $e) {
            throw new TranslatorException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @param string|null $languageCode Language code or null for the default language.
     * @throws TranslatorException If language is not found.
     */
    public function getLanguage(?string $languageCode): Language
    {
        if ($languageCode === null) {
            return $this->getDefaultLanguage();
        }

        $this->assertLanguageExists($languageCode);
        return $this->languages[$languageCode];
    }

    /** @return array<string, Language> */
    public function getAvailableLanguages(bool $enabledOnly = true): array
    {
        return $enabledOnly
            ? array_filter($this->languages, fn($language) => $language->isEnabled())
            : $this->languages;
    }

    /**
     * Returns an associative array of language names.
     *
     * @return array<string,string>
     */
    public function getLanguageNames(bool $enabledOnly = true): array
    {
        $languages = $this->getAvailableLanguages($enabledOnly);

        $names = [];
        foreach ($languages as $language) {
            $names[$language->getCode()] = $language->getName();
        }

        return $names;
    }

    public function getDefaultLanguage(): Language
    {
        return $this->defaultLanguage;
    }

    public function getCurrentLanguage(): Language
    {
        return $this->currentLanguage;
    }

    public function switchLanguage(Language $language): void
    {
        $this->currentLanguage = $language;
    }

    /**
     * Returns the secondary language code.
     *
     * @param string $fallback Fallback language code if secondary is not found
     * @throws InvalidArgumentException If no secondary language is found
     *
     * @todo Secondary language must be configurable
     * @todo Move to LanguageRepository
     * @todo Remove fallback parameter
     */
    public function getSecondaryLanguage(string $fallback = 'en'): string
    {
        foreach ($this->languages as $language) {
            if (!$language->isDefault() && $language->isEnabled()) {
                return $language->getCode();
            }
        }

        return $fallback;
    }
}
