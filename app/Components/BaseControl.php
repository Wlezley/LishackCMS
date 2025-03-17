<?php

declare(strict_types=1);

namespace App\Components;

use App\Models\TranslationManager;
use Nette\Application\UI\Control;
use Nette\Application\UI\Template;

class BaseControl extends Control
{
    protected string $lang = DEFAULT_LANG;

    /** @var TranslationManager */
    protected TranslationManager $translationManager;

    /** @var array<string,string> $cmsConfig */
    protected array $cmsConfig = [];

    /** @var null|array<string,string> $param */
    protected ?array $param = [];

    public function injectTranslationManager(TranslationManager $translationManager): void
    {
        $this->translationManager = $translationManager;
    }

    protected function createTemplate(?string $class = null): Template
    {
        $template = parent::createTemplate($class);

        if (!isset($this->translationManager)) {
            throw new \RuntimeException('TranslationManager is not available in ' . static::class);
        }

        // Translations
        $template->_ = fn($key) => $this->translationManager->get($key, $this->lang); // @phpstan-ignore property.notFound

        return $template;
    }

    public function setLang(string $lang): void
    {
        $this->lang = $lang;
    }

    public function getLang(): string
    {
        return $this->lang;
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
