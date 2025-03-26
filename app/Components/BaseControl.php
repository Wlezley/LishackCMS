<?php

declare(strict_types=1);

namespace App\Components;

use App\Models\ConfigManager;
use App\Models\TranslationManager;
use Nette\Application\UI\Control;
use Nette\Application\UI\Template;

class BaseControl extends Control
{
    /** @var TranslationManager */
    protected TranslationManager $translationManager;

    /** @var ConfigManager */
    protected ConfigManager $configManager;

    /** @var array<string,string> $cmsConfig */
    protected array $cmsConfig = [];

    /** @var null|array<string,string> $param */
    protected ?array $param = [];

    public function setTranslationManager(TranslationManager $translationManager): void
    {
        $this->translationManager = $translationManager;
    }

    public function getTranslationManager(): TranslationManager
    {
        return $this->translationManager;
    }

    public function setConfigManager(ConfigManager $configManager): void
    {
        $this->configManager = $configManager;
    }

    public function getConfigManager(): ConfigManager
    {
        return $this->configManager;
    }

    /** @throws \RuntimeException If TranslationManager or ConfigManager is not available. */
    protected function createTemplate(?string $class = null): Template
    {
        $template = parent::createTemplate($class);

        if (!isset($this->translationManager)) {
            throw new \RuntimeException('TranslationManager is not available in ' . static::class);
        }

        if (!isset($this->configManager)) {
            throw new \RuntimeException('ConfigManager is not available in ' . static::class);
        }

        // Translations
        $template->_ = fn($key) => $this->translationManager->get($key); // @phpstan-ignore property.notFound
        $template->_F = fn($key, $values) => $this->translationManager->getf($key, null, $values); // @phpstan-ignore property.notFound

        // Configuration
        $template->_C = fn($key) => $this->configManager->get($key); // @phpstan-ignore property.notFound

        return $template;
    }

    /**
     * Retrieves a translated text for a given key in a specified language.
     *
     * This is a shorthand wrapper for `TranslationManager::get()`.
     *
     * @param string $key The translation key.
     * @param string|null $lang Optional language code (defaults to current language).
     * @throws \RuntimeException If TranslationManager is not available.
     * @return string The translated text, or the key itself if not found.
     */
    public function t(string $key, ?string $lang = null): string
    {
        if (!isset($this->translationManager)) {
            throw new \RuntimeException('TranslationManager is not available in ' . static::class);
        }

        return $this->translationManager->get($key, $lang);
    }

    /**
     * Translates a key and formats the translation with the given values.
     *
     * This is a wrapper around `TranslationManager::getf()`, which:
     * - Retrieves the translated string for the given key.
     * - Uses `vsprintf()` to format the string with the provided values.
     * - Falls back to returning the key if the translation is missing or formatting fails.
     *
     * @param string $key The translation key.
     * @param mixed ...$values Values to be formatted into the translated string.
     * @return string The formatted translated text, or the key itself if translation is unavailable.
     * @throws \RuntimeException If `TranslationManager` is not available.
     */
    public function tf(string $key, mixed ...$values): string
    {
        if (!isset($this->translationManager)) {
            throw new \RuntimeException('TranslationManager is not available in ' . static::class);
        }

        return $this->translationManager->getf($key, null, $values);
    }

    /**
     * Retrieves a configuration value for a given key.
     *
     * This is a shorthand wrapper for `ConfigManager::get()`.
     *
     * @param string $key The configuration key.
     * @throws \RuntimeException If ConfigManager is not available.
     * @return string|null The configuration value, or null if not found.
     */
    public function c(string $key): ?string
    {
        if (!isset($this->configManager)) {
            throw new \RuntimeException('ConfigManager is not available in ' . static::class);
        }

        return $this->configManager->get($key);
    }

    /** @param array<string,string> $cmsConfig */
    public function setCmsConfig(array $cmsConfig): void
    {
        $this->cmsConfig = $cmsConfig;
    }

    /** @return array<string,string> */
    public function getCmsConfig(): array
    {
        return $this->cmsConfig;
    }

    /** @param null|array<string,string> $param */
    public function setParam(?array $param): void
    {
        $this->param = $param === null ? [] : $param;
    }

    /** @return array<string,string> */
    public function getParam(): array
    {
        return $this->param;
    }
}
