<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presenters;

use App\Components\Admin\IConfigEditorFactory;
use App\Components\Admin\IConfigSeoFormFactory;
use App\Components\Admin\IConfigWebsiteFormFactory;

class ConfigPresenter extends SecuredPresenter
{
    /** @var IConfigEditorFactory @inject */
    public IConfigEditorFactory $configEditor;

    /** @var IConfigWebsiteFormFactory @inject */
    public IConfigWebsiteFormFactory $configWebsiteForm;

    /** @var IConfigSeoFormFactory @inject */
    public IConfigSeoFormFactory $configSeoForm;

    public function renderDefault(): void
    {
        $this->redirect('Config:editor');
    }

    public function renderEditor(): void
    {
    }

    public function renderWebsite(): void
    {
    }

    public function renderSeo(): void
    {
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

    protected function createComponentConfigWebsiteForm(): \App\Components\Admin\ConfigWebsiteForm
    {
        $form = $this->configWebsiteForm->create();

        $form->onSuccess = function(string $message): void {
            $this->flashMessage($message, 'info');
            $this->redirect('Config:website');
        };

        $form->onError = function(string $message): void {
            $this->flashMessage($message, 'danger');
        };

        return $form;
    }

    protected function createComponentConfigSeoForm(): \App\Components\Admin\ConfigSeoForm
    {
        $form = $this->configSeoForm->create();

        $form->onSuccess = function(string $message): void {
            $this->flashMessage($message, 'info');
            $this->redirect('Config:seo');
        };

        $form->onError = function(string $message): void {
            $this->flashMessage($message, 'danger');
        };

        return $form;
    }
}
