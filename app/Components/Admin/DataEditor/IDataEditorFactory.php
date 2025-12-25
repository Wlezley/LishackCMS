<?php

declare(strict_types=1);

namespace App\Components\Admin\DataEditor;

interface IDataEditorFactory
{
    public function create(): DataEditor;
}
