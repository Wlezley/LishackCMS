<?php

declare(strict_types=1);

namespace App\Components;

use App\Models\ConfigManager;
use App\Models\TranslationManager;
use Nette\Application\UI\Control;
use Nette\Application\UI\Template;

class BaseControl extends Control
{
    use \App\Models\Config;
    use \App\Models\Translation;

    /** @var TranslationManager */
    protected TranslationManager $translationManager;

    /** @var ConfigManager */
    protected ConfigManager $configManager;

    /** @var array<string,string> $cmsConfig */
    protected array $cmsConfig = [];

    /** @var null|array<string,string> $param */
    protected ?array $param = [];

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
