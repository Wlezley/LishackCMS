<?php

declare(strict_types=1);

namespace App\Components\Admin;

use App\Components\BaseControl;
use App\Models\UserManager;
use App\Models\UserRole;
use Contributte\Datagrid\Datagrid;
use Nette\Application\UI\Presenter;
use Nette\Database\Explorer;
use Nette\Security\User;
use Nette\Utils\Json;

class UserListGrid extends BaseControl
{
    protected UserRole $userRole;
    protected Presenter $presenter;

    public function __construct(
        public Explorer $db,
        private UserManager $userManager,
        protected User $user
    ) {
        $this->userRole = new UserRole($user);
    }

    public function setPresenter(Presenter $presenter):void
    {
        $this->presenter = $presenter;
    }

    public function createGrid(): Datagrid
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
        $grid->addColumnText('name', $this->t('name'))
            ->setSortable()
            ->setAlign('start');

        // FULL NAME
        $grid->addColumnText('full_name', $this->t('full-name'))
            ->setSortable()
            ->setAlign('start');

        // E-MAIL
        $grid->addColumnText('email', $this->t('e-mail'))
            ->setSortable()
            ->setAlign('start');


        // USER ROLE ---->>
        $roleOptions = [
            ['role' => 'guest',     'icon' => 'person-circle-question', 'class' => 'btn-secondary'],
            ['role' => 'user',      'icon' => 'user',                   'class' => 'btn-success'],
            ['role' => 'redactor',  'icon' => 'pencil',                 'class' => 'btn-info'],
            ['role' => 'manager',   'icon' => 'hammer',                 'class' => 'btn-warning'],
            ['role' => 'admin',     'icon' => 'user-ninja',             'class' => 'btn-danger'],
        ];

        $roleColumn = $grid->addColumnStatus('role', $this->t('permissions'));
        foreach ($roleOptions as $option) {
            if (!in_array($option['role'], $editableRoles)) {
                continue;
            }
            $roleColumn->addOption($option['role'], $this->t('user.role.' . $option['role']))
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
        $grid->addColumnStatus('deleted', $this->t('deleted'))
            ->addOption(1, $this->t('yes'))
                ->setIcon('ban')
                ->setClass('btn-secondary' . $columnDeleted_class)
                ->endOption()
            ->addOption(0, $this->t('no'))
                ->setIcon('xmark')
                ->setClass('btn-success' . $columnDeleted_class)
                ->endOption()
            ->setSortable()
            ->setAlign('center')
            ->onChange[] = [$this, 'setDeleted_Callback'];

        // ENABLED flag
        $columnEnabled_class = $columnEnabled_allowed ? '' : ' disabled';
        $grid->addColumnStatus('enabled', $this->t('active'))
            ->addOption(1, $this->t('yes'))
                ->setIcon('check')
                ->setClass('btn-success' . $columnEnabled_class)
                ->endOption()
            ->addOption(0, $this->t('no'))
                ->setIcon('ban')
                ->setClass('btn-danger' . $columnEnabled_class)
                ->endOption()
            ->setSortable()
            ->setAlign('center')
            ->onChange[] = [$this, 'setEnabled_Callback'];

        // CREATED (datetime)
        $grid->addColumnDateTime('created', $this->t('created'))
            ->setAlign('end')
            ->setFormat('j.n.Y');

        // LAST LOGIN (datetime)
        $grid->addColumnDateTime('last_login', $this->t('last-login'))
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

    // TODO: Move all of this logic below to the UserManager (?)

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
        if (!isset($this->presenter)) {
            throw new \Nette\Application\InvalidPresenterException('Presenter has not been set. Use the setPresenter first.', 1);
        }

        if ($this->presenter->isAjax()) {
            $userData = $this->userManager->get((int)$id);

            if ($userData['role'] == $role) {
                return; // No change was made...
            } elseif ($this->user->getId() == (int)$id) {
                $this->presenter->flashMessage('Uživatel nemůže měnit vlastní oprávnění.', 'danger');
            } elseif ($id == 1) {
                $this->presenter->flashMessage('Oprávnění hlavního administrátora nelze měnit.', 'danger');
            } elseif ($this->userRole->isLessOrEqualsThan($userData['role']) && $this->userRole->isNot('admin')) {
                $this->presenter->flashMessage('Nemůžete měnit oprávnění uživatele, který má stejné (nebo vyšší) oprávnění jako Vy.', 'danger');
            } elseif ($this->userRole->isLessOrEqualsThan($role) && $this->userRole->isNot('admin')) {
                $this->presenter->flashMessage('Uživateli nelze udělit stejné, ani vyšší oprávnění, než jaké máte Vy.', 'danger');
            } elseif (!$this->userManager->setRole((int)$id, $role)) {
                $this->presenter->flashMessage('U vybraného uživatele se nepodařilo změnit oprávnění.', 'danger');
            }

            $this->presenter->redrawControl();
        }
    }

    public function setEnabled_Callback(string $id, string $enabled): void
    {
        if (!isset($this->presenter)) {
            throw new \Nette\Application\InvalidPresenterException('Presenter has not been set. Use the setPresenter first.', 1);
        }

        if ($this->presenter->isAjax()) {
            $userData = $this->userManager->get((int)$id);
            $actionName = $enabled === '1' ? 'povolit' : 'zakázat';

            if ($userData['enabled'] == $enabled) {
                return; // No change was made...
            } elseif ($this->user->getId() == (int)$id) {
                $this->presenter->flashMessage("Uživatel nemůže $actionName sám sebe.", 'danger');
            } elseif ($id == 1) {
                $this->presenter->flashMessage("Hlavního administrátora nelze $actionName přes administraci.", 'danger');
            } elseif ($this->userRole->isLessOrEqualsThan($userData['role']) && $this->userRole->isNot('admin')) {
                $this->presenter->flashMessage("Nemůžete $actionName uživatele, který má stejné (nebo vyšší) oprávnění jako Vy.", 'danger');
            } elseif (!$this->userManager->setEnabled((int)$id, $enabled === '1')) {
                $this->presenter->flashMessage("Vybraného uživatele se nepodařilo $actionName.", 'danger');
            }

            $this->presenter->redrawControl();
        }
    }

    public function setDeleted_Callback(string $id, string $deleted): void
    {
        if (!isset($this->presenter)) {
            throw new \Nette\Application\InvalidPresenterException('Presenter has not been set. Use the setPresenter first.', 1);
        }

        if ($this->presenter->isAjax()) {
            $userData = $this->userManager->get((int)$id);
            $actionName = $deleted === '1' ? 'smazat' : 'obnovit';

            if ($userData['deleted'] == $deleted) {
                return; // No change was made...
            } elseif ($this->user->getId() == (int)$id) {
                $this->presenter->flashMessage("Uživatel nemůže $actionName sám sebe.", 'danger');
            } elseif ($id == 1) {
                $this->presenter->flashMessage("Hlavního administrátora nelze $actionName přes administraci.", 'danger');
            } elseif ($this->userRole->isLessOrEqualsThan($userData['role']) && $this->userRole->isNot('admin')) {
                $this->presenter->flashMessage("Nemůžete $actionName uživatele, který má stejné (nebo vyšší) oprávnění jako Vy.", 'danger');
            } elseif (!$this->userManager->setDeleted((int)$id, $deleted === '1')) {
                $this->presenter->flashMessage("Vybraného uživatele se nepodařilo $actionName.", 'danger');
            }

            $this->presenter->redrawControl();
        }
    }
}
