<?php

declare(strict_types=1);

namespace App\Components\Admin;

interface IConfigEditorFactory
{
    public function create(): ConfigEditor;
}
