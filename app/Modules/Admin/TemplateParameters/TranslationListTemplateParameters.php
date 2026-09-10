<?php

declare(strict_types=1);

namespace App\Modules\Admin\TemplateParameters;

use App\Components\Admin\TranslationList\TranslationList;
use App\Entity\Translation\Translation;

class TranslationListTemplateParameters extends TranslationTemplateParameters
{
    public TranslationList $control;
    public \Closure $getJson;
    /** @var Translation[] */
    public array $translations;
}
