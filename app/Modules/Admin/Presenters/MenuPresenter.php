<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presenters;

class MenuPresenter extends SecuredPresenter
{
    public function renderDefault(): void
    {
        $this->template->title = 'Menu';
    }

    public function renderCreate(): void
    {
        $this->template->title = 'Vytvoření menu';
    }

    public function renderEdit(int $id = 0): void
    {
        $this->template->title = "Editace menu ID: $id";
        $this->template->menuId = $id;
    }

    public function actionDelete(int $id): void
    {
        $this->flashMessage("Menu ID: $id bylo odstraněno (včetně vnořených položek).", 'info');
        $this->redirect('Menu:');
    }
}
