<?php

declare(strict_types=1);

namespace App\Components\Admin;

interface ITranslationFormFactory
{
    public function create(): TranslationForm;
}
