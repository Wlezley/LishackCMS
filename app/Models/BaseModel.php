<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Translation\TranslatorTrait;
use App\Service\Config\ConfigService;
use App\Trait\ConfigTrait;
use Nette\SmartObject;

abstract class BaseModel
{
    use SmartObject;

    use ConfigTrait;
    use TranslatorTrait;

    protected mixed $data = null;

    public function __construct(
        protected ConfigService $configService,
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
//        $lang = $lang ?? $this->configService->get('DEFAULT_LANG');
//        Assert::notNull($lang, 'Default language is not set');
//        $this->languageService->setCurrentLanguage($lang);
//    }
//
//    public function getLang(): string
//    {
//        return $this->languageService->getCurrentLanguage();
//    }
}
