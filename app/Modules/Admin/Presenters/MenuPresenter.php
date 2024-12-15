<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presenters;

use App\Models\MenuException;
use App\Models\MenuManager;
use Tracy\Debugger;

class MenuPresenter extends SecuredPresenter
{
    public function __construct(
        private MenuManager $menuManager
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
        $menuItem = $this->menuManager->get($id);

        $this->template->title = "Editace menu $id: " . $menuItem['name'];
        $this->template->menuItem = $menuItem;
    }

    public function actionDelete(int $id): void
    {
        if ($this->user->isInRole('admin')) {
            $this->menuManager->delete($id);
            $this->flashMessage("Menu ID: $id bylo odstraněno.", 'info');
        } else {
            $this->flashMessage('K odstranění položky nemáte oprávnění.', 'danger');
        }

        $this->redirect('Menu:');
    }

    public function handleLoad(): void
    {
        if (!$this->isAjax()) {
            $this->redirect('this');
        }

        $this->sendJson([
            'nodes' => $this->menuManager->getSortableTree()
        ]);
    }

    public function handleSave(): void
    {
        if (!$this->isAjax()) {
            $this->redirect('this');
        }

        $data = $this->getHttpRequest()->getPost();

        try {
            $this->menuManager->updatePosition($data);
        } catch (MenuException $e) {
            $this->sendJson([
                'status' => 'error',
                'message' => $e->getMessage(),
                'debug' => Debugger::$productionMode === Debugger::Development,
            ]);
        }

        $this->sendJson([
            'status' => 'success',
            'message' => 'Menu saved successfully',
            'nodes' => $this->menuManager->getSortableTree(),
            'debug' => Debugger::$productionMode === Debugger::Development,
        ]);
    }
}
