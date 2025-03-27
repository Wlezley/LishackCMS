<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presenters;

use App\Components\Admin\ICategoryFormFactory;
use App\Models\CategoryException;
use App\Models\CategoryManager;
use Nette\Utils\Json;

class MenuPresenter extends SecuredPresenter
{
    /** @var CategoryManager @inject */
    public CategoryManager $categoryManager;

    /** @var ICategoryFormFactory @inject */
    public ICategoryFormFactory $categoryForm;

    public function renderDefault(): void
    {
        $this->template->sortable = $this->userHavePermissionsTo('move'); // or 'sort' ???
    }

    public function renderCreate(): void
    {
    }

    public function renderEdit(?int $id): void
    {
        if (!$id) {
            $this->redirect(':default');
        }

        $this->template->title .= " ID: $id";
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

        try {
            if ($this->userHavePermissionsTo('move')) {
                $this->categoryManager->updatePosition((array) $this->getHttpRequest()->getPost());
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

    // ##########################################
    // ###             COMPONENTS             ###
    // ##########################################

    protected function createComponentCategoryForm(): \App\Components\Admin\CategoryForm
    {
        $form = $this->categoryForm->create();
        $id = $this->getParameter('id');

        $form->setCategoryManager($this->categoryManager);

        if ($id) {
            $form->setOrigin($form::OriginEdit);
            $param = $this->categoryManager->getById((int) $id);

            if ($param) {
                $form->setParam($param);
            } else {
                $this->flashMessage('Kategorie nebyla nalezena'); // TODO: Translate
            }
        } else {
            $form->setOrigin($form::OriginCreate);
            $form->setParam($this->getHttpRequest()->getPost('param'));
        }

        $form->onSuccess = function(string $message): void {
            $this->flashMessage($message, 'info');
            $this->redirect('Menu:');
        };

        $form->onError = function(string $message): void {
            $this->flashMessage($message, 'danger');
        };

        return $form;
    }
}
