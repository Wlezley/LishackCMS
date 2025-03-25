<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presenters;

class DataPresenter extends SecuredPresenter
{
    public function renderDefault(): void
    {
        $this->template->containerList = [];
    }

    public function renderCreate(): void
    {
    }

    public function renderEdit(int $id = 0): void
    {
        $this->template->title .= " ID: $id";
        $this->template->containerId = $id;
    }

    public function actionDelete(int $id): void
    {
        $this->flashMessage("Datový kontejner ID: $id byl odstraněn.", 'info');
        $this->redirect('Data:');
    }
}
