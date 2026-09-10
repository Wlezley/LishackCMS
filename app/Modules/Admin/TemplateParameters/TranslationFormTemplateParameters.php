<?php

declare(strict_types=1);

namespace App\Modules\Admin\TemplateParameters;

use App\Components\Admin\TranslationForm\TranslationForm;

class TranslationFormTemplateParameters extends TranslationTemplateParameters
{
    public TranslationForm $control;
}
