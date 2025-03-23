<?php

declare(strict_types=1);

namespace App\Components;

use App\Models\TranslationManager;
use Nette\Application\UI\Control;
use Nette\Application\UI\Template;

class BaseControl extends Control
{
    /** @var TranslationManager */
    protected TranslationManager $translationManager;

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

    /** @throws \RuntimeException If TranslationManager is not available. */
    protected function createTemplate(?string $class = null): Template
    {
        $template = parent::createTemplate($class);

        if (!isset($this->translationManager)) {
            throw new \RuntimeException('TranslationManager is not available in ' . static::class);
        }

        // Translations
        $template->_ = fn($key) => $this->translationManager->get($key); // @phpstan-ignore property.notFound

        return $template;
    }

    /**
     * Returns the translation string for a given key and language.
     *
     * If $lang is null, the default or current language is used.
     * If the translation is not found, the key itself is returned.
     *
     * @param string      $key  The translation key.
     * @param string|null $lang The target language code (null = current language).
     *
     * @throws \RuntimeException If TranslationManager is not available.
     *
     * @return string The translated text, the key as fallback.
     */
    public function t(string $key, ?string $lang = null): string
    {
        if (!isset($this->translationManager)) {
            throw new \RuntimeException('TranslationManager is not available in ' . static::class);
        }

        return $this->translationManager->get($key, $lang);
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
