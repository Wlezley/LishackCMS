<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presenters;

use App\Components\Admin\IFileExplorerFactory;

class FileExplorerPresenter extends SecuredPresenter
{
    /** @var IFileExplorerFactory @inject */
    public IFileExplorerFactory $fileExplorer;

    public function renderDefault(): void
    {
    }

    public function renderDirectory(int $id): void
    {
    }

    // public function actionEditFolder(int $id): void
    // {
    // }

    // public function actionEditFile(int $id): void
    // {
    // }

    // ##########################################
    // ###             COMPONENTS             ###
    // ##########################################

    protected function createComponentFileExplorer(): \App\Components\Admin\FileExplorer
    {
        $control = $this->fileExplorer->create();
        return $control;
    }
}
