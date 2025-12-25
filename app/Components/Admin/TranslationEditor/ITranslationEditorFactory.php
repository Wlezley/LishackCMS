<?php

declare(strict_types=1);

namespace App\Components\Admin\TranslationEditor;

interface ITranslationEditorFactory
{
    public function create(): TranslationEditor;
}
