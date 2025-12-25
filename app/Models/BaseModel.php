<?php

declare(strict_types=1);

namespace App\Models;

use Nette\Database\Explorer;
use Nette\SmartObject;
use Webmozart\Assert\Assert;

abstract class BaseModel
{
    use SmartObject;

    use \App\Models\Config;
    use \App\Models\Translation;

    protected mixed $data = null;

    public function __construct(
        protected Explorer $db,
        protected ConfigManager $configManager,
        protected TranslationManager $translationManager
    ) {
    }

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
        $this->data = null;
    }

    /**
     * @throws TranslationException
     */
    public function setLang(?string $lang = null): void
    {
        $lang = $lang ?? $this->configManager->get('DEFAULT_LANG');
        Assert::notNull($lang, 'Default language is not set');
        $this->translationManager->setCurrentLanguage($lang);
    }

    public function getLang(): string
    {
        return $this->translationManager->getCurrentLanguage();
    }
}
