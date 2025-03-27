<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presenters;

use App\Models\CategoryException;
use App\Models\CategoryManager;
use Nette\Utils\Json;

class MenuPresenter extends SecuredPresenter
{
    public function __construct(
        private CategoryManager $categoryManager
    ) {}

    public function renderDefault(): void
    {
        $this->template->sortable = $this->userHavePermissionsTo('move'); // or 'sort' ???
    }

    public function renderCreate(): void
    {
    }

    public function renderEdit(int $id = 0): void
    {
        try {
            $item = $this->categoryManager->get($id);

            $this->template->title .= " ID: $id";
            $this->template->item = $item;

            $this->template->jsonData = Json::encode([
                'id' => $item['id'],
                'name' => $item['name'],
                'modal' => [
                    'title' => $this->t('modal.title.confirm-delete'),
                    'body' => $this->tf('modal.body.delete-menu', $item['name'])
                ],
            ]);

            $this->template->editable = $this->userHavePermissionsTo('edit');

            // TODO: Test render for the "Menu Parent" tree <select> elem.
            $this->template->tree = $this->categoryManager->getTree();
            bdump($this->template->tree, "MENU TREE");

        } catch (CategoryException $e) {
            $this->flashMessage('Chyba: ' . $e->getMessage(), 'danger');
        }
    }

    public function actionDelete(int $id): void
    {
        if ($this->userHavePermissionsTo('delete')) {
            $this->categoryManager->delete($id);
            $this->flashMessage("Menu ID: $id bylo odstraněno.", 'info');
        } else {
            $this->flashMessage('K odstranění menu nemáte oprávnění.', 'danger');
        }

        $this->redirect('Menu:');
    }

    // ##########################################
    // ###                AJAX                ###
    // ##########################################

    public function handleDelete(): void
    {
        if (!$this->isAjax()) {
            $this->redirect('this');
        }

        $data = $this->getHttpRequest()->getPost();

        try {
            if ($this->userHavePermissionsTo('delete')) {
                $this->categoryManager->delete((int) $data['id']);
                $this->flashMessage("Menu ID: " . $data['id'] . " bylo odstraněno.", 'info');
            } else {
                $this->flashMessage('K odstranění menu nemáte oprávnění.', 'danger');
                $this->sendJson([
                    'status' => 'error',
                    'message' => 'Insufficient user permissions.',
                ]);
            }
        } catch (CategoryException $e) {
            $this->sendJson([
                'status' => 'error',
                'message' => $e->getMessage(),
            ]);
        }

        $this->sendJson([
            'status' => 'success',
            'message' => 'Menu item deleted successfully',
            'id' => $data['id'],
            'call' => 'removeFromList',
        ]);
    }

    public function handleLoad(): void
    {
        if (!$this->isAjax()) {
            $this->redirect('this');
        }

        $this->sendJson([
            'nodes' => $this->categoryManager->getSortableTree()
        ]);
    }

    public function handleUpdatePosition(): void
    {
        if (!$this->isAjax()) {
            $this->redirect('this');
        }

        $data = $this->getHttpRequest()->getPost();

        try {
            if ($this->userHavePermissionsTo('move')) {
                $this->categoryManager->updatePosition($data);
            } else {
                $this->flashMessage('K přesunutí menu nemáte oprávnění.', 'danger');
                $this->sendJson([
                    'status' => 'error',
                    'message' => 'Insufficient user permissions.',
                ]);
            }
        } catch (CategoryException $e) {
            $this->sendJson([
                'status' => 'error',
                'message' => $e->getMessage(),
            ]);
        }

        $this->sendJson([
            'status' => 'success',
            'message' => 'Menu position successfully updated',
            'nodes' => $this->categoryManager->getSortableTree(),
        ]);
    }

    // TODO: Unify roles, create an ACL system...
    private function userHavePermissionsTo(string $action): bool
    {
        switch ($action) {
            case 'edit':
                return $this->userRole->isInArray(['manager', 'admin']);

            case 'move':
            case 'sort':
                return $this->userRole->isInArray(['manager', 'admin']);

            case 'delete':
                return $this->userRole->isInArray(['manager', 'admin']);
        }

        return false;
    }
}
