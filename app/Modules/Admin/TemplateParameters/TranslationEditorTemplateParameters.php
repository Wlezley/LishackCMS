<?php

declare(strict_types=1);

namespace App\Modules\Admin\TemplateParameters;

use App\Components\Admin\TranslationEditor\TranslationEditor;
use App\Entity\Language\Language;
use App\Entity\Translation\Translation;

class TranslationEditorTemplateParameters extends TranslationTemplateParameters
{
    public TranslationEditor $control;
    /** @var Translation[] */
    public array $translations;
    public string $defaultLang;
    public string $targetLang;
    /** @var Language[] */
    public array $languages;
}
