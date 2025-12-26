<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Config\ConfigManager;
use App\Models\Config\ConfigTrait;
use App\Models\Translation\TranslatorTrait;
use Nette\Database\Explorer;
use Nette\SmartObject;

abstract class BaseModel
{
    use SmartObject;

    use ConfigTrait;
    use TranslatorTrait;

    protected mixed $data = null;

    public function __construct(
        protected Explorer $db,
        protected ConfigManager $configManager,
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

    // TODO: Implement language support
//    public function setLang(?string $lang = null): void
//    {
//        $lang = $lang ?? $this->configManager->get('DEFAULT_LANG');
//        Assert::notNull($lang, 'Default language is not set');
//        $this->languageService->setCurrentLanguage($lang);
//    }
//
//    public function getLang(): string
//    {
//        return $this->languageService->getCurrentLanguage();
//    }
}
