<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presenters;

use App\Models\StorageSystem\FileManager;

class FileExplorerPresenter extends SecuredPresenter
{
    public function __construct(
        // private FileManager $fileManager
    ) {}

    public function renderDefault(): void
    {
    }
}
