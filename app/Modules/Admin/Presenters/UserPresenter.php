<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presenters;


class UserPresenter extends _SecuredPresenter
{
    public function renderDefault()
    {
        $this->template->title = 'Seznam uživatelů';
        $this->template->userData = $this->user->identity->data;
    }

    public function renderCreate()
    {
        $this->template->title = 'Vytvoření nového uživatele';
    }

    public function renderEdit(int $id = 0)
    {
        $this->template->title = "Editace uživatele ID: $id";
        $this->template->userId = $id;
    }

    public function actionDelete(int $id)
    {
        $this->flashMessage("Uživatel ID: $id byl odstraněn.", 'info');
        $this->redirect('User:');
    }
}
