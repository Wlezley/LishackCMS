<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presenters;

use App\Models\UserException;
use App\Models\UserManager;
use Contributte\Datagrid\Datagrid;
use Nette\Utils\Json;

class UserPresenter extends SecuredPresenter
{
    public function __construct(
        private UserManager $userManager
    ) {}

    public function renderDefault(): void
    {
        $this->template->title = 'Uživatelské účty';
    }

    public function renderCreate(): void
    {
        $this->template->title = 'Vytvoření nového uživatele';
    }

    public function renderEdit(int $id = 0): void
    {
        $this->template->title = 'Editace uživatele';

        try {
            $item = $this->userManager->get($id);

            $this->template->title .= " ID: $id";
            $this->template->item = $item;

            $this->template->jsonData = Json::encode([
                'id' => $item['id'],
                'name' => $item['name'],
                'modal' => [
                    'title' => 'Potvrzení o smazání',
                    'body' => 'Opravdu chcete uživatele <strong>' . $item['name'] . '</strong> smazat?'
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
            $this->setDeleted_Callback($data['id'], '1');
        } catch (UserException $e) {
            $this->sendJson([
                'status' => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }

    // public function handleLoad(): void
    // {
    //     if (!$this->isAjax()) {
    //         $this->redirect('this');
    //     }
    //     $this->redrawControl();
    // }

    // public function handleSave(): void
    // {
    //     if (!$this->isAjax()) {
    //         $this->redirect('this');
    //     }
    //     $this->redrawControl();
    // }

    // ##########################################
    // ###              DATAGRID              ###
    // ##########################################

    public function createComponentUserList(): DataGrid
    {
        $grid = new Datagrid();

        // Permission settings
        $userIsAdmin = $this->userRole->is('admin');
        $editableRoles = $this->userRole->getLowerList(true);
        $columnDeleted_allowed = $this->userRole->isInArray(['manager', 'admin']);
        $columnEnabled_allowed = $this->userRole->isInArray(['manager', 'admin']);

        if ($userIsAdmin) {
            $grid->setDataSource($this->db->table(UserManager::TABLE_NAME)->select('*'));
        } else {
            $deletedFilter = $columnDeleted_allowed ? ['0', '1'] : '0';
            $enabledFilter = $columnEnabled_allowed ? ['1', '0'] : '1';
            $grid->setDataSource($this->db->table(UserManager::TABLE_NAME)->select('*')->where([
                'role' => $editableRoles,
                'deleted' => $deletedFilter,
                'enabled' => $enabledFilter,
            ]));
        }

        // Datagrid settings
        $grid->setDefaultSort(['id' => 'ASC']);
        $grid->setDefaultPerPage(25);
        $grid->setItemsPerPageList([25, 50, 100], true);
        $grid->allowRowsInlineEdit(function() { return false; });

        // ID
        $grid->addColumnText('id', 'ID')
            ->setSortable()
            ->setAlign('center');

        // NAME
        $grid->addColumnText('name', 'Jméno')
            ->setSortable()
            ->setAlign('start');

        // FULL NAME
        $grid->addColumnText('full_name', 'Celé jméno')
            ->setSortable()
            ->setAlign('start');

        // E-MAIL
        $grid->addColumnText('email', 'E-mail')
            ->setSortable()
            ->setAlign('start');


        // USER ROLE ---->>
        $roleOptions = [
            ['role' => 'guest',     'name' => 'Host',       'icon' => 'person-circle-question', 'class' => 'btn-secondary'],
            ['role' => 'user',      'name' => 'Uživatel',   'icon' => 'user',                   'class' => 'btn-success'],
            ['role' => 'redactor',  'name' => 'Redaktor',   'icon' => 'pencil',                 'class' => 'btn-info'],
            ['role' => 'manager',   'name' => 'Moderátor',  'icon' => 'hammer',                 'class' => 'btn-warning'],
            ['role' => 'admin',     'name' => 'Správce',    'icon' => 'user-ninja',             'class' => 'btn-danger'],
        ];

        $roleColumn = $grid->addColumnStatus('role', 'Oprávnění');
        foreach ($roleOptions as $option) {
            if (!in_array($option['role'], $editableRoles)) {
                continue;
            }
            $roleColumn->addOption($option['role'], $option['name'])
                ->setIcon($option['icon'])
                ->setClass($option['class'])
                ->endOption();
        }
        $roleColumn->setSortable()
            ->setAlign('center')
            ->onChange[] = [$this, 'setRole_Callback'];
        // <<---- USER ROLE


        // DELETED flag
        $columnDeleted_class = $columnDeleted_allowed ? '' : ' disabled';
        $grid->addColumnStatus('deleted', 'Smazaný')
            ->addOption(1, 'Ano')
                ->setIcon('ban')
                ->setClass('btn-secondary' . $columnDeleted_class)
                ->endOption()
            ->addOption(0, 'Ne')
                ->setIcon('xmark')
                ->setClass('btn-success' . $columnDeleted_class)
                ->endOption()
            ->setSortable()
            ->setAlign('center')
            ->onChange[] = [$this, 'setDeleted_Callback'];

        // ENABLED flag
        $columnEnabled_class = $columnEnabled_allowed ? '' : ' disabled';
        $grid->addColumnStatus('enabled', 'Aktivní')
            ->addOption(1, 'Ano')
                ->setIcon('check')
                ->setClass('btn-success' . $columnEnabled_class)
                ->endOption()
            ->addOption(0, 'Ne')
                ->setIcon('ban')
                ->setClass('btn-danger' . $columnEnabled_class)
                ->endOption()
            ->setSortable()
            ->setAlign('center')
            ->onChange[] = [$this, 'setEnabled_Callback'];

        // CREATED (datetime)
        $grid->addColumnDateTime('created', 'Vytvořeno')
            ->setAlign('end')
            ->setFormat('j.n.Y');

        // LAST LOGIN (datetime)
        $grid->addColumnDateTime('last_login', 'Přihlášení')
            ->setAlign('end')
            ->setFormat('j.n.Y H:m:s')
            ->setReplacement(['' => 'N/A']);

        // ACTION EDIT
        $grid->addAction(':edit', '')
            ->setClass('btn btn-xs btn-primary')
            ->setIcon('pencil');

        // ACTION DELETE
        // $deleteActionCallback = new \Ublaboo\DataGrid\Column\Action\Confirmation\CallbackConfirmation([$this, 'encodeData_Callback']); // Deprecated (?)
        $deleteActionCallback = new \Contributte\Datagrid\Column\Action\Confirmation\CallbackConfirmation([$this, 'encodeData_Callback']);
        $grid->addAction(':delete', '')
            ->setClass('btn btn-xs btn-danger')
            ->setIcon('eraser')
            ->setDataAttribute('bs-toggle', 'modal')
            ->setDataAttribute('bs-target', '#deleteUserConfirmModal')
            ->setConfirmation($deleteActionCallback);

        // Actions callback
        $grid->allowRowsAction(':edit', [$this, 'allowActionEdit_Callback']);
        $grid->allowRowsAction(':delete', [$this, 'allowActionDelete_Callback']);

        return $grid;
    }

    // ##########################################
    // ###              CALLBACK              ###
    // ##########################################

    public function allowActionEdit_Callback(object $item): bool
    {
        // Unable to edit SUPERADMIN
        if ($item->id == 1) {
            return false;
        }
        // SUPERADMIN can edit all
        if ($this->user->getId() === 1) {
            return true;
        }
        // User can edit himself
        if ($this->user->getId() == $item->id) {
            return true;
        }
        // User cannot edit other users in the same or higher role
        if ($this->userRole->isLessOrEqualsThan($item->role)) {
            return false;
        }

        // Allow the rest
        return true;
    }

    public function allowActionDelete_Callback(object $item): bool
    {
        // Unable to delete SUPERADMIN
        if ($item->id == 1) {
            return false;
        }
        // SUPERADMIN can delete (almost) all
        if ($this->user->getId() === 1) {
            return true;
        }
        // User cannot delete himself
        if ($this->user->getId() == $item->id) {
            return false;
        }
        // User cannot delete other users in the same or higher role
        if ($this->userRole->isLessOrEqualsThan($item->role)) {
            return false;
        }
        // Only administrators and moderators can delete users
        if ($this->userRole->isInArray(['manager', 'admin'])) {
            return true;
        }

        return false;
    }

    public function encodeData_Callback(object $item): string
    {
        $data = Json::encode([
            'id' => $item->id,
            'name' => $item->name,
            'full_name' => $item->full_name,
            'email' => $item->email,
            'role' => $item->role,
            'deleted' => $item->deleted,
            'enabled' => $item->enabled,
            'modal' => [
                'title' => 'Potvrzení o smazání',
                'body' => 'Opravdu chcete uživatele <strong>' . $item->name . '</strong> smazat?'
            ]
        ]);

        return $data;
    }

    // TODO: TRANSLATIONS !!!

    public function setRole_Callback(string $id, string $role): void
    {
        if ($this->isAjax()) {
            $userData = $this->userManager->get((int)$id);

            if ($userData['role'] == $role) {
                return; // No change was made...
            } elseif ($this->user->getId() == (int)$id) {
                $this->flashMessage('Uživatel nemůže měnit vlastní oprávnění.', 'danger');
            } elseif ($id == 1) {
                $this->flashMessage('Oprávnění hlavního administrátora nelze měnit.', 'danger');
            } elseif ($this->userRole->isLessOrEqualsThan($userData['role']) && $this->userRole->isNot('admin')) {
                $this->flashMessage('Nemůžete měnit oprávnění uživatele, který má stejné (nebo vyšší) oprávnění jako Vy.', 'danger');
            } elseif ($this->userRole->isLessOrEqualsThan($role) && $this->userRole->isNot('admin')) {
                $this->flashMessage('Uživateli nelze udělit stejné, ani vyšší oprávnění, než jaké máte Vy.', 'danger');
            } elseif (!$this->userManager->setRole((int)$id, $role)) {
                $this->flashMessage('U vybraného uživatele se nepodařilo změnit oprávnění.', 'danger');
            }

            $this->redrawControl();
        }
    }

    public function setEnabled_Callback(string $id, string $enabled): void
    {
        if ($this->isAjax()) {
            $userData = $this->userManager->get((int)$id);
            $actionName = $enabled === '1' ? 'povolit' : 'zakázat';

            if ($userData['enabled'] == $enabled) {
                return; // No change was made...
            } elseif ($this->user->getId() == (int)$id) {
                $this->flashMessage("Uživatel nemůže $actionName sám sebe.", 'danger');
            } elseif ($id == 1) {
                $this->flashMessage("Hlavního administrátora nelze $actionName přes administraci.", 'danger');
            } elseif ($this->userRole->isLessOrEqualsThan($userData['role']) && $this->userRole->isNot('admin')) {
                $this->flashMessage("Nemůžete $actionName uživatele, který má stejné (nebo vyšší) oprávnění jako Vy.", 'danger');
            } elseif (!$this->userManager->setEnabled((int)$id, $enabled === '1')) {
                $this->flashMessage("Vybraného uživatele se nepodařilo $actionName.", 'danger');
            }

            $this->redrawControl();
        }
    }

    public function setDeleted_Callback(string $id, string $deleted): void
    {
        if ($this->isAjax()) {
            $userData = $this->userManager->get((int)$id);
            $actionName = $deleted === '1' ? 'smazat' : 'obnovit';

            if ($userData['deleted'] == $deleted) {
                return; // No change was made...
            } elseif ($this->user->getId() == (int)$id) {
                $this->flashMessage("Uživatel nemůže $actionName sám sebe.", 'danger');
            } elseif ($id == 1) {
                $this->flashMessage("Hlavního administrátora nelze $actionName přes administraci.", 'danger');
            } elseif ($this->userRole->isLessOrEqualsThan($userData['role']) && $this->userRole->isNot('admin')) {
                $this->flashMessage("Nemůžete $actionName uživatele, který má stejné (nebo vyšší) oprávnění jako Vy.", 'danger');
            } elseif (!$this->userManager->setDeleted((int)$id, $deleted === '1')) {
                $this->flashMessage("Vybraného uživatele se nepodařilo $actionName.", 'danger');
            }

            $this->redrawControl();
        }
    }

    // ##########################################
    // ###             COMPONENTS             ###
    // ##########################################


    protected function createComponentUserForm(): \App\Components\Admin\UserForm
    {
        $form = $this->userForm->create();
        // $form->setCmsConfig($this->cmsConfig);
        $form->setParam($this->getHttpRequest()->getPost('param'));
        // $form->setUserManager($this->userManager);

        $form->onSuccess = function(\Nette\Utils\ArrayHash $values): void {
            bdump($values, 'ON SUCCESS VALUES');

            if (isset($values['id'])) {
                bdump('EDIT', "STATUS");
                try {
                    $id = $values['id'];
                    unset($values['id']);

                    $this->userManager->update((int)$id, (array)$values);
                    $this->flashMessage('Uživatel byl upraven', 'info');
                    // $this->redirect('this');
                } catch(UserException $e) {
                    $this->flashMessage($e->getMessage(), 'danger');
                } catch (\InvalidArgumentException $e) {
                    $this->flashMessage($e->getMessage(), 'danger');
                }
            } else {
                bdump('CREATE', "STATUS");
                try {
                    $userID = $this->userManager->create((array)$values);
                    $this->flashMessage("Uživatel byl vytvořen (ID: $userID).", 'info');
                    $this->redirect('User:default');
                } catch(UserException $e) {
                    $this->flashMessage($e->getMessage(), 'danger');
                    // $this->redirect('this#form', $form->getParameters());
                }
            }
        };

        $form->onError = function(string $message): void {
            bdump($message, 'ON ERROR MSG');
            $this->flashMessage($message, 'danger');
            // $this->redirect('this#form', $form->getParameters());
        };

        return $form;
    }
}
