<?php

declare(strict_types=1);

namespace App\Modules\Admin\TemplateParameters;

use App\Entity\Language\Language;
use App\Modules\Admin\Presenters\TranslationPresenter;

class TranslationTemplateParameters extends BaseTemplateParameters
{
    public TranslationPresenter $presenter;
    public Language $language;
    /** @var Language[] */
    public array $availableLanguages;
    public ?string $search;
}
