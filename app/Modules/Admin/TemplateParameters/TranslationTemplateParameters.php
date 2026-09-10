<?php

declare(strict_types=1);

namespace App\Modules\Admin\TemplateParameters;

use App\Entity\Language\Language;
use App\Modules\Admin\Presenters\TranslationPresenter;

class TranslationTemplateParameters extends BaseTemplateParameters
{
    public TranslationPresenter $presenter;
    public string $lang; // TODO: This is just language code. Remove it and use $language->getCode()
    public Language $language;
    /** @var Language[] */
    public array $langList; // TODO: rename to languages and refactor inheritor classes
    public ?string $search;
}
