<?php

declare(strict_types=1);

namespace App\Modules\Admin\TemplateParameters;

use App\Components\Admin\TranslationForm\TranslationForm;
use App\Entity\Language\Language;

class TranslationFormTemplateParameters extends TranslationTemplateParameters
{
    public TranslationForm $control;
    /** @var Language[] */
    public array $languageList; // TODO: remove after refactoring TranslationTemplateParameters (and rename to languages in template/component)
}
