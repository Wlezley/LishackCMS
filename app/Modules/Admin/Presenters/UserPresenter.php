<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presenters;

use App\Components\Admin\IUserFormFactory;
use App\Components\Admin\UserListGrid;
use App\Models\UserException;
use App\Models\UserManager;
use Contributte\Datagrid\Datagrid;
use Nette\Utils\Json;

class UserPresenter extends SecuredPresenter
{
    /** @var IUserFormFactory @inject */
    public IUserFormFactory $userForm;

    public function __construct(
        private UserManager $userManager,
        private UserListGrid $userListGrid
    ) {
        $this->userListGrid->setPresenter($this);
    }

    public function renderDefault(): void
    {
    }

    public function renderCreate(): void
    {
    }

    public function renderEdit(int $id): void
    {
        try {
            $item = $this->userManager->get($id);

            $this->template->title .= " ID: $id";
            $this->template->item = $item;

            $this->template->jsonData = Json::encode([
                'id' => $item['id'],
                'name' => $item['name'],
                'modal' => [
                    'title' => $this->t('modal.title.confirm-delete'),
                    'body' => $this->tf('modal.body.delete-user', $item['name'])
                ],
            ]);
        } catch (\Exception $e) {
            $this->flashMessage('Chyba: ' . $e->getMessage(), 'danger');
        }
    }

    public function actionDelete(int $id): void
    {
        // TODO: Conditions from setDeleted_Callback()
        // TODO: Unify roles, create an ACL system...
        if ($this->user->isInRole('admin')) {
            $this->userManager->setDeleted($id, true);
            $this->flashMessage("Uživatel ID: $id byl odstraněn.", 'info');
        } else {
            $this->flashMessage('K odstranění uživatele nemáte oprávnění.', 'danger');
        }

        $this->redirect('User:');
    }

    public function handleDelete(): void
    {
        if (!$this->isAjax()) {
            $this->redirect('this');
        }

        $data = $this->getHttpRequest()->getPost();

        try {
            $this->userListGrid->setDeleted_Callback($data['id'], '1'); // TODO: Move to UserManager (?)
        } catch (UserException $e) {
            $this->sendJson([
                'status' => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }

    // ##########################################
    // ###             COMPONENTS             ###
    // ##########################################

    protected function createComponentUserList(): Datagrid
    {
        // $this->userListGrid->setPresenter($this);
        $this->userListGrid->setTranslationManager($this->translationManager);
        $this->userListGrid->setConfigManager($this->configManager);
        return $this->userListGrid->createGrid();
    }

    protected function createComponentUserForm(): \App\Components\Admin\UserForm
    {
        $form = $this->userForm->create();
        $id = $this->getParameter('id');

        if ($id) {
            try {
                $userData = $this->userManager->get((int) $id);
                $form->setParam($userData);
                $form->setOrigin($form::OriginEdit);
            } catch (\Exception $e) {
                $this->flashMessage('Chyba při čtení dat uživatele: ' . $e->getMessage(), 'danger');
            }
        } else {
            $form->setParam($this->getHttpRequest()->getPost('param'));
            $form->setOrigin($form::OriginCreate);
        }

        $form->onSuccess = function(string $message): void {
            $this->flashMessage($message, 'info');
            $this->redirect('User:default');
        };

        $form->onError = function(string $message): void {
            $this->flashMessage($message, 'danger');
        };

        return $form;
    }
}
