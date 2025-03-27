<?php

declare(strict_types=1);

namespace App\Models;

use Nette\SmartObject;
use Nette\Database\Explorer;

abstract class BaseModel
{
    use SmartObject;

    use \App\Models\Config;
    use \App\Models\Translation;

    protected mixed $data = [];

    public function __construct(
        protected Explorer $db,
        protected ConfigManager $configManager,
        protected TranslationManager $translationManager
    ) {}

    public function load(): void
    {
    }

    public function reload(): void
    {
        $this->invalidate();
        $this->load();
    }

    public function invalidate(): void
    {
        $this->data = [];
    }

    public function setLang(?string $lang = null): void
    {
        $lang = $lang ?? $this->configManager->get('DEFAULT_LANG');
        $this->translationManager->setCurrentLanguage($lang);
    }

    public function getLang(): string
    {
        return $this->translationManager->getCurrentLanguage();
    }
}
