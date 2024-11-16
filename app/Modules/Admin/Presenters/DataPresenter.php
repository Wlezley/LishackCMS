<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presenters;


class DataPresenter extends _SecuredPresenter
{
    public function renderDefault()
    {
        $this->template->title = 'Seznam datových kontejnerů';
        $this->template->containerList = [];
    }

    public function renderCreate()
    {
        $this->template->title = 'Vytvoření datového kontejneru';
    }

    public function renderEdit(int $id = 0)
    {
        $this->template->title = "Editace datového kontejneru ID: $id";
        $this->template->containerId = $id;
    }

    public function actionDelete(int $id)
    {
        $this->flashMessage("Datový kontejner ID: $id byl odstraněn.", 'info');
        $this->redirect('Data:');
    }
}
