<?php

declare(strict_types=1);

namespace App\Components\Admin;

interface ITranslationEditorFactory
{
    public function create(): TranslationEditor;
}
