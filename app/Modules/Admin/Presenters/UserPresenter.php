<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presenters;

use App\Components\Admin\UserForm\IUserFormFactory;
use App\Components\Admin\UserForm\UserForm;
use App\Components\Admin\UserList\UserListGrid;
use App\Entity\User\UserRepositoryInterface;
use App\Exception\UserException;
use Contributte\Datagrid\Datagrid;
use Contributte\Datagrid\Exception\DatagridColumnStatusException;
use Contributte\Datagrid\Exception\DatagridException;
use Nette\Utils\Json;
use Webmozart\Assert\Assert;

class UserPresenter extends SecuredPresenter
{
    /** @var IUserFormFactory @inject */
    public IUserFormFactory $userForm;

    /** @var UserRepositoryInterface @inject */
    public UserRepositoryInterface $userRepository;

    public function __construct(
        private readonly UserListGrid $userListGrid,
    ) {
        parent::__construct();

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
            $user = $this->userRepository->getById($id);
            Assert::notNull($user);

            $this->template->title .= " ID: $id";
            $this->template->item = $user;

            $this->template->jsonData = Json::encode([
                'id' => $user->getId(),
                'name' => $user->getUserName(),
                'modal' => [
                    'title' => $this->t('modal.title.confirm-delete'),
                    'body' => $this->tf('modal.body.delete-user', $user->getUserName()),
                ],
            ]);
        } catch (\Exception $e) {
            $this->flashMessage('Chyba: ' . $e->getMessage(), 'danger');
        }
    }

    public function actionDelete(int $userId): void
    {
        // TODO: Conditions from setDeleted_Callback()
        // TODO: Unify roles, create an ACL system...
        // TODO: TRANSLATE FLASH MESSAGES !!!
        if ($this->user->isInRole('admin')) {
            $user = $this->userRepository->getById($userId);
            if ($user === null) {
                throw new \Exception('User not found');
            }

            $user->setDeleted(true);
            $this->userRepository->save($user);

            $this->flashMessage("Uživatel ID: $userId byl odstraněn.", 'info');
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
            $this->userListGrid->setDeletedCallback($data['id'], '1'); // TODO: Move to UserManager (?)
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

    /**
     * @throws DatagridColumnStatusException
     * @throws DatagridException
     */
    protected function createComponentUserList(): Datagrid
    {
        // $this->userListGrid->setPresenter($this);
        $this->userListGrid->setTranslationService($this->translationService);
        $this->userListGrid->setConfigManager($this->configManager);
        return $this->userListGrid->createGrid();
    }

    protected function createComponentUserForm(): UserForm
    {
        $form = $this->userForm->create();
        $userId = $this->getParameter('id');

        // TODO: TRANSLATE FLASH MESSAGES !!!
        if ($userId) {
            try {
                $form->setUser($this->userRepository->getById($userId));
                $form->setOrigin($form::OriginEdit); // TODO: Create FormOrigin enum
            } catch (\Exception $e) {
                $this->flashMessage('Chyba při čtení dat uživatele: ' . $e->getMessage(), 'danger');
            }
        } else {
            $form->setParam($this->getHttpRequest()->getPost('param'));
            $form->setOrigin($form::OriginCreate);
        }

        $form->onSuccess = function (string $message): void {
            $this->flashMessage($message, 'info');
            $this->redirect('User:default');
        };

        $form->onError = function (string $message): void {
            $this->flashMessage($message, 'danger');
        };

        return $form;
    }
}
