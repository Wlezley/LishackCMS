<?php

declare(strict_types=1);

namespace App\Components;

use App\Models\Config\ConfigManager;
use App\Models\Config\ConfigTrait;
use App\Models\Translation\TranslationManager;
use App\Models\Translation\TranslationTrait;
use App\Models\UrlGenerator\UrlGenerator;
use Nette\Application\UI\Control;
use Nette\Application\UI\Template;
use RuntimeException;

class BaseControl extends Control
{
    use ConfigTrait;
    use TranslationTrait;

    /** @var ConfigManager @inject */
    public ConfigManager $configManager;

    /** @var TranslationManager @inject */
    public TranslationManager $translationManager;

    /** @var UrlGenerator @inject */
    public UrlGenerator $urlGenerator;

    /** @var array<string,string> $cmsConfig */
    protected array $cmsConfig = [];

    /** @var null|array<string,int|string> $param */
    protected ?array $param = [];

    protected ?string $templatePath = null;

    /**
     * @throws RuntimeException If TranslationManager or ConfigManager is not available.
     */
    protected function createTemplate(?string $class = null): Template
    {
        $template = parent::createTemplate($class);

        if (!isset($this->translationManager)) {
            throw new RuntimeException('TranslationManager is not available in ' . static::class);
        }

        if (!isset($this->configManager)) {
            throw new RuntimeException('ConfigManager is not available in ' . static::class);
        }

        // Translations
        // phpcs:disable
        $template->_ = fn($key) => $this->translationManager->get($key); // @phpstan-ignore property.notFound
        $template->_F = fn($key, $values) => $this->translationManager->getf($key, null, $values); // @phpstan-ignore property.notFound

        // Configuration
        $template->_C = fn($key) => $this->configManager->get($key); // @phpstan-ignore property.notFound
        // phpcs:enable

        return $template;
    }

    public function setConfigManager(ConfigManager $configManager): void
    {
        $this->configManager = $configManager;
    }

    public function getConfigManager(): ConfigManager
    {
        return $this->configManager;
    }

    public function setTranslationManager(TranslationManager $translationManager): void
    {
        $this->translationManager = $translationManager;
    }

    public function getTranslationManager(): TranslationManager
    {
        return $this->translationManager;
    }

    /** @return array<string,string> */
    public function getCmsConfig(): array
    {
        return $this->cmsConfig;
    }

    /** @param  array<string,string> $cmsConfig */
    public function setCmsConfig(array $cmsConfig): void
    {
        $this->cmsConfig = $cmsConfig;
    }

    /** @param null|array<string,int|string> $param */
    public function setParam(?array $param): void
    {
        $this->param = $param === null ? [] : $param;
    }

    /** @return null|array<string,int|string> */
    public function getParam(): ?array
    {
        return $this->param;
    }

    public function getTemplatePath(): ?string
    {
        return $this->templatePath;
    }
}
