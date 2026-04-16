<?php

declare(strict_types=1);

namespace App\Components\Admin\DatasetEditor;

interface IDatasetEditorFactory
{
    public function create(): DatasetEditor;
}
