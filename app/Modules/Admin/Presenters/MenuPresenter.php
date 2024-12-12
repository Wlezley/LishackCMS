<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presenters;

use App\Models\Menu;

class MenuPresenter extends SecuredPresenter
{
    public function __construct(
        private Menu $menuManager
    ) {}

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

    public function handleLoad(): void
    {
        if (!$this->isAjax()) {
            $this->redirect('this');
        }

        $nodes = $this->menuManager->getSortableTree();
        $this->sendJson(['nodes' => $nodes]);
    }

    public function handleSave(): void
    {
        if (!$this->isAjax()) {
            $this->redirect('this');
        }

        $data = $this->getHttpRequest()->getPost();
        bdump($data, "DATA TO SAVE");

        $status = 'error';
        $message = 'Unhandled error';

        if (is_array($data)) {
            // TODO: Save data ($this->menuManager->...)

            $status = 'success';
            $message = 'Menu saved successfully';
        } else {
            $status = 'error';
            $message = 'Invalid menu structure';
        }

        $this->sendJson(['status' => $status, 'message' => $message, 'debug' => !DEBUG]);
    }
}
