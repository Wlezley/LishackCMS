<?php

declare(strict_types=1);

namespace App\Components\Admin;

interface IDataEditorFactory
{
    public function create(): DataEditor;
}
