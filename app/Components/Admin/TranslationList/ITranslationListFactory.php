<?php

declare(strict_types=1);

namespace App\Components\Admin;

interface ITranslationListFactory
{
    public function create(): TranslationList;
}
