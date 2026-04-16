<?php

declare(strict_types=1);

namespace App\Components;

use App\Models\Config\ConfigManager;
use App\Models\Config\ConfigTrait;
use App\Models\Translation\LanguageService;
use App\Models\Translation\Translator;
use App\Models\Translation\TranslatorTrait;
use App\Models\UrlGenerator\UrlGenerator;
use Nette\Application\UI\Control;
use Nette\Application\UI\Template;
use RuntimeException;
use Webmozart\Assert\Assert;

class BaseControl extends Control
{
    use ConfigTrait;
    use TranslatorTrait;

    /** @var ConfigManager @inject */
    public ConfigManager $configManager;

    /** @var LanguageService @inject */
    public LanguageService $languageService;

    /** @var Translator @inject */
    public Translator $translator;

    /** @var UrlGenerator @inject */
    public UrlGenerator $urlGenerator;

    /** @var array<string,string> $cmsConfig */
    protected array $cmsConfig = [];

    /** @var null|array<string,int|string> $param */
    protected ?array $param = [];

    protected ?string $templatePath = null;

    /**
     * @throws RuntimeException If Translator or ConfigManager is not available.
     */
    protected function createTemplate(?string $class = null): Template
    {
        $template = parent::createTemplate($class);

        // CONFIGURATOR
        if (!isset($this->configManager)) {
            throw new RuntimeException('ConfigManager is not available in ' . static::class);
        }

        // phpcs:ignore
        $template->_C = fn($key) => $this->configManager->get($key); // @phpstan-ignore property.notFound

        // TRANSLATOR
        if (!isset($this->translator)) {
            throw new RuntimeException('Translator is not available in ' . static::class);
        }

        // phpcs:disable
        $template->_ = fn($key) => $this->translator->translate($key); // @phpstan-ignore property.notFound
        $template->_F = fn($key, $values) => $this->translator->translateFormat($key, null, $values); // @phpstan-ignore property.notFound
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

    public function getLanguageService(): LanguageService
    {
        return $this->languageService;
    }

    public function setLanguageService(LanguageService $languageService): void
    {
        $this->languageService = $languageService;
    }

    public function getTranslator(): Translator
    {
        return $this->translator;
    }

    public function setTranslator(Translator $translator): void
    {
        $this->translator = $translator;
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

    public function getMixedParam(string $key): mixed
    {
        if (!isset($this->param[$key])) {
            return null;
        }
        return $this->param[$key];
    }

    public function getIntParam(string $key): ?int
    {
        if (!isset($this->param[$key])) {
            return null;
        }
        Assert::numeric($this->param[$key], "Parameter '$key' must be numeric");
        return (int) $this->param[$key];
    }

    /** @return int<0, max>|null */
    public function getPositiveIntParam(string $key): ?int
    {
        if (!isset($this->param[$key])) {
            return null;
        }
        Assert::numeric($this->param[$key], "Parameter '$key' must be numeric");
        Assert::range($this->param[$key], 0, PHP_INT_MAX, "Parameter '$key' must be positive integer");

        /** @var int<0, max> $value */
        $value = $this->param[$key];
        return $value;
    }

    /** @return int<min, -1>|null */
    public function getNegativeIntParam(string $key): ?int
    {
        if (!isset($this->param[$key])) {
            return null;
        }
        Assert::numeric($this->param[$key], "Parameter '$key' must be numeric");
        Assert::range($this->param[$key], PHP_INT_MIN, -1, "Parameter '$key' must be negative integer");

        /** @var int<min, -1> $value */
        $value = $this->param[$key];
        return $value;
    }

    public function getStringParam(string $key): ?string
    {
        if (!isset($this->param[$key])) {
            return null;
        }
        Assert::string($this->param[$key], "Parameter '$key' must be string");
        return (string) $this->param[$key];
    }

    public function getTemplatePath(): ?string
    {
        return $this->templatePath;
    }
}
