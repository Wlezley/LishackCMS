<?php

declare(strict_types=1);

namespace App\Models\Translation;

use App\Exception\TranslatorException;
use App\Models\Config\ConfigManager;
use Nette\Database\Explorer;
use Webmozart\Assert\Assert;
use Webmozart\Assert\InvalidArgumentException;

class LanguageService
{
    public const string TABLE_NAME = 'lang';

    /** @var array<string,array<string,mixed>> */
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
                $item = $row->toArray();
                $key = $item['lang'];
                $this->languages[$key] = $item;
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
    public function assertLanguageExists(string $lang): void
    {
        try {
            Assert::keyExists($this->languages, $lang, "Language '$lang' not found.");
        } catch (InvalidArgumentException $e) {
            throw new TranslatorException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @return array<string,mixed>
     * @throws TranslatorException If language is not found.
     */
    public function getLanguage(string $lang): array
    {
        $this->assertLanguageExists($lang);
        return $this->languages[$lang];
    }

    /** @return array<string,array<string,mixed>> */
    public function getAvailableLanguages(bool $enabledOnly = true): array
    {
        return $enabledOnly
            ? array_filter($this->languages, fn($lang) => $lang['enabled'] ?? false)
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

        foreach ($languages as $key => $data) {
            $names[$key] = $data['name'];
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
        foreach ($this->languages as $lang => $data) {
            if ($data['default'] == 1) {
                return $lang;
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
        foreach ($this->languages as $lang => $data) {
            if ($data['default'] == 0 && $data['enabled'] == 1) {
                return $lang;
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
