<?php

declare(strict_types=1);

namespace App\Components\Admin\TranslationList;

interface ITranslationListFactory
{
    public function create(): TranslationList;
}
