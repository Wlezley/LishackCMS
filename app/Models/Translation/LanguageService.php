<?php

declare(strict_types=1);

namespace App\Models\Translation;

use App\Dto\Localization\LanguageDto;
use App\Exception\TranslatorException;
use App\Models\Config\ConfigManager;
use Nette\Database\Explorer;
use Webmozart\Assert\Assert;
use Webmozart\Assert\InvalidArgumentException;

class LanguageService
{
    public const string TABLE_NAME = 'lang';

    /** @var array<string, LanguageDto> */
    private array $languages = [];

    /** @var string Currently selected language */
    private string $currentLanguage;

    public function __construct(
        private readonly Explorer $db,
        private readonly ConfigManager $configManager,
    ) {
        $this->load();
        $this->currentLanguage = $this->getDefaultLanguage(); // Bootup default language
    }

    private function load(): void
    {
        if (empty($this->languages)) {
            foreach ($this->db->table(self::TABLE_NAME)->fetchAll() as $row) {
                $languageDto = LanguageDto::fromEntity($row);
                $languageCode = $languageDto->lang;
                $this->languages[$languageCode] = $languageDto;
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
     * @throws TranslatorException If language is not found.
     */
    public function getLanguage(string $languageCode): LanguageDto
    {
        $this->assertLanguageExists($languageCode);
        return $this->languages[$languageCode];
    }

    /** @return array<string, LanguageDto> */
    public function getAvailableLanguages(bool $enabledOnly = true): array
    {
        return $enabledOnly
            ? array_filter($this->languages, fn($languageDto) => $languageDto->enabled)
            : $this->languages;
    }

    /**
     * Returns an associative array of language names.
     *
     * @return array<string,string>
     */
    public function getLanguageNames(bool $enabledOnly = true): array
    {
        $names = [];
        $languages = $this->getAvailableLanguages($enabledOnly);

        foreach ($languages as $languageCode => $languageDto) {
            $names[$languageCode] = $languageDto->name;
        }

        return $names;
    }

    /**
     * Returns the default language code.
     *
     * @param string|null $fallback Fallback language code if default is not found, defaults to config value
     */
    public function getDefaultLanguage(?string $fallback = null): string
    {
        foreach ($this->languages as $languageCode => $languageDto) {
            if ($languageDto->default) {
                return $languageCode;
            }
        }

        if ($fallback === null) {
            return $this->configManager->get('DEFAULT_LANG') ?? 'en';
        }

        return $fallback;
    }

    /**
     * Returns the secondary language code.
     *
     * @param string $fallback Fallback language code if secondary is not found
     * @throws InvalidArgumentException If no secondary language is found
     *
     * @todo Secondary language must be configurable
     */
    public function getSecondaryLanguage(string $fallback = 'en'): string
    {
        foreach ($this->languages as $languageCode => $languageDto) {
            if (!$languageDto->default && $languageDto->enabled) {
                return $languageCode;
            }
        }

        return $fallback;
    }

    public function getCurrentLanguage(): string
    {
        return $this->currentLanguage;
    }

    public function setCurrentLanguage(string $currentLanguage): void
    {
        $this->currentLanguage = $currentLanguage;
    }
}
