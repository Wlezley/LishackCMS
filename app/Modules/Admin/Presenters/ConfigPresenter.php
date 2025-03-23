<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presenters;

use App\Components\Admin\IConfigEditorFactory;

class ConfigPresenter extends SecuredPresenter
{
    /** @var IConfigEditorFactory @inject */
    public IConfigEditorFactory $configEditor;

    public function renderDefault(): void
    {
        $this->template->title = 'Nastavení';
    }

    public function renderEditor(): void
    {
        $this->template->title = 'Editor Nastavení';
    }

    // ##########################################
    // ###             COMPONENTS             ###
    // ##########################################

    protected function createComponentConfigEditor(): \App\Components\Admin\ConfigEditor
    {
        $control = $this->configEditor->create();

        $control->onSuccess = function(string $message): void {
            $this->flashMessage($message, 'info');
            $this->redirect('Config:editor');
        };

        $control->onError = function(string $message): void {
            $this->flashMessage($message, 'danger');
        };

        return $control;
    }
}
