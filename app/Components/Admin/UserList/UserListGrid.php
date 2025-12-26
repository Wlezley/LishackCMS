<?php

declare(strict_types=1);

namespace App\Components\Admin\UserList;

use AllowDynamicProperties;
use App\Components\BaseControl;
use App\Exception\TranslatorException;
use App\Exception\UserException;
use App\Models\Translation\TranslatorTrait;
use App\Models\User\UserManager;
use App\Models\User\UserRole;
use Contributte\Datagrid\Datagrid;
use Contributte\Datagrid\Exception\DatagridColumnStatusException;
use Contributte\Datagrid\Exception\DatagridException;
use Nette\Application\InvalidPresenterException;
use Nette\Application\UI\Presenter;
use Nette\Database\Explorer;
use Nette\Database\Table\ActiveRow;
use Nette\Security\User;
use Nette\Utils\Json;
use Nette\Utils\JsonException;

/**
 * @property string $id
 * @property string $name
 * @property string $full_name
 * @property string $email
 * @property string $role
 * @property string $deleted
 * @property string $enabled
 */
#[AllowDynamicProperties]
class UserListGrid extends BaseControl
{
    use TranslatorTrait;

    protected UserRole $userRole;
    protected Presenter $presenter;

    public function __construct(
        public Explorer $db,
        private UserManager $userManager,
        protected User $user,
    ) {
        $this->userRole = new UserRole($user);
    }

    public function setPresenter(Presenter $presenter): void
    {
        $this->presenter = $presenter;
    }

    /**
     * @throws DatagridColumnStatusException
     * @throws DatagridException
     */
    public function createGrid(): Datagrid
    {
        $grid = new Datagrid();

        // Permission settings
        $userIsAdmin = $this->userRole->is('admin');
        $editableRoles = $this->userRole->getLowerList(true);
        $columnDeletedAllowed = $this->userRole->isInArray(['manager', 'admin']);
        $columnEnabledAllowed = $this->userRole->isInArray(['manager', 'admin']);

        if ($userIsAdmin) {
            $grid->setDataSource($this->db->table(UserManager::TABLE_NAME)->select('*')->where('id != 0'));
        } else {
            $deletedFilter = $columnDeletedAllowed ? ['0', '1'] : '0';
            $enabledFilter = $columnEnabledAllowed ? ['1', '0'] : '1';
            $grid->setDataSource($this->db->table(UserManager::TABLE_NAME)->select('*')->where([
                'id != 0',
                'role' => $editableRoles,
                'deleted' => $deletedFilter,
                'enabled' => $enabledFilter,
            ]));
        }

        $paginationItemLimit = (int) $this->c('PAGINATION_PAGE_ITEMS');
        $paginationItemLimitList = [
            $paginationItemLimit,
            $paginationItemLimit * 2,
            $paginationItemLimit * 3,
        ];

        // Datagrid settings
        $grid->setDefaultSort(['id' => 'ASC']);
        $grid->setDefaultPerPage($paginationItemLimit);
        $grid->setItemsPerPageList($paginationItemLimitList, true);
        $grid->allowRowsInlineEdit(function () {
            return false;
        });

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
            ->onChange[] = [$this, 'setRoleCallback'];
        // <<---- USER ROLE


        // DELETED flag
        $columnDeletedClass = $columnDeletedAllowed ? '' : ' disabled';
        $grid->addColumnStatus('deleted', $this->t('deleted'))
            ->addOption(1, $this->t('yes'))
                ->setIcon('ban')
                ->setClass('btn-secondary' . $columnDeletedClass)
                ->endOption()
            ->addOption(0, $this->t('no'))
                ->setIcon('xmark')
                ->setClass('btn-success' . $columnDeletedClass)
                ->endOption()
            ->setSortable()
            ->setAlign('center')
            ->onChange[] = [$this, 'setDeletedCallback'];

        // ENABLED flag
        $columnEnabledClass = $columnEnabledAllowed ? '' : ' disabled';
        $grid->addColumnStatus('enabled', $this->t('active'))
            ->addOption(1, $this->t('yes'))
                ->setIcon('check')
                ->setClass('btn-success' . $columnEnabledClass)
                ->endOption()
            ->addOption(0, $this->t('no'))
                ->setIcon('ban')
                ->setClass('btn-danger' . $columnEnabledClass)
                ->endOption()
            ->setSortable()
            ->setAlign('center')
            ->onChange[] = [$this, 'setEnabledCallback'];

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
        $deleteActionCallback = new \Contributte\Datagrid\Column\Action\Confirmation\CallbackConfirmation([$this, 'encodeDataCallback']);
        $grid->addAction(':delete', '')
            ->setClass('btn btn-xs btn-danger')
            ->setIcon('eraser')
            ->setDataAttribute('bs-toggle', 'modal')
            ->setDataAttribute('bs-target', '#deleteUserConfirmModal')
            ->setConfirmation($deleteActionCallback);

        // Actions callback
        $grid->allowRowsAction(':edit', [$this, 'allowActionEditCallback']);
        $grid->allowRowsAction(':delete', [$this, 'allowActionDeleteCallback']);

        return $grid;
    }

    // TODO: Move all of this logic below to the UserManager (?)

    public function allowActionEditCallback(ActiveRow $item): bool
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

    public function allowActionDeleteCallback(ActiveRow $item): bool
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

    /**
     * @throws JsonException
     * @throws TranslatorException
     */
    public function encodeDataCallback(ActiveRow $item): string
    {
        return Json::encode([
            'id' => $item->id,
            'name' => $item->name,
            'full_name' => $item->full_name, // phpcs:ignore
            'email' => $item->email,
            'role' => $item->role,
            'deleted' => $item->deleted,
            'enabled' => $item->enabled,
            'modal' => [
                'title' => $this->t('modal.title.confirm-delete'),
                'body' => $this->tf('modal.body.delete-user', $item->name),
            ],
        ]);
    }

    /**
     * @throws UserException
     * @throws InvalidPresenterException
     * @throws TranslatorException
     */
    public function setRoleCallback(string $id, string $role): void
    {
        if (!isset($this->presenter)) {
            throw new InvalidPresenterException('Presenter has not been set. Use the setPresenter first.', 1);
        }

        if ($this->presenter->isAjax()) {
            $userData = $this->userManager->get((int)$id);

            if ($userData['role'] == $role) {
                return;
            } elseif ($this->user->getId() == (int)$id) {
                $this->presenter->flashMessage($this->t('user.role-callback.himself'), 'danger');
            } elseif ($id == 1) {
                $this->presenter->flashMessage($this->t('user.role-callback.admin'), 'danger');
            } elseif ($this->userRole->isLessOrEqualsThan($userData['role']) && $this->userRole->isNot('admin')) {
                $this->presenter->flashMessage($this->t('user.role-callback.same-role'), 'danger');
            } elseif ($this->userRole->isLessOrEqualsThan($role) && $this->userRole->isNot('admin')) {
                $this->presenter->flashMessage($this->t('user.role-callback.role-elevation'), 'danger');
            } elseif (!$this->userManager->setRole((int)$id, $role)) {
                $this->presenter->flashMessage($this->t('user.role-callback.update-failed'), 'danger');
            }

            $this->presenter->redrawControl();
        }
    }

    /**
     * @throws UserException
     * @throws InvalidPresenterException
     * @throws TranslatorException
     */
    public function setEnabledCallback(string $id, string $enabled): void
    {
        if (!isset($this->presenter)) {
            throw new InvalidPresenterException('Presenter has not been set. Use the setPresenter first.', 1);
        }

        if ($this->presenter->isAjax()) {
            $userData = $this->userManager->get((int)$id);
            $actionName = $enabled === '1' ? $this->t('enable') : $this->t('disable');

            if ($userData['enabled'] == $enabled) {
                return;
            } elseif ($this->user->getId() == (int)$id) {
                $this->presenter->flashMessage($this->tf('user.enabled-callback.himself', $actionName), 'danger');
            } elseif ($id == 1) {
                $this->presenter->flashMessage($this->tf('user.enabled-callback.admin', $actionName), 'danger');
            } elseif ($this->userRole->isLessOrEqualsThan($userData['role']) && $this->userRole->isNot('admin')) {
                $this->presenter->flashMessage($this->tf('user.enabled-callback.same-role', $actionName), 'danger');
            } elseif (!$this->userManager->setEnabled((int)$id, $enabled === '1')) {
                $this->presenter->flashMessage($this->tf('user.enabled-callback.update-failed', $actionName), 'danger');
            }

            $this->presenter->redrawControl();
        }
    }

    /**
     * @throws InvalidPresenterException
     * @throws UserException
     * @throws TranslatorException
     */
    public function setDeletedCallback(string $id, string $deleted): void
    {
        if (!isset($this->presenter)) {
            throw new InvalidPresenterException('Presenter has not been set. Use the setPresenter first.', 1);
        }

        if ($this->presenter->isAjax()) {
            $userData = $this->userManager->get((int)$id);
            $actionName = $deleted === '1' ? $this->t('delete') : $this->t('restore');

            if ($userData['deleted'] == $deleted) {
                return;
            } elseif ($this->user->getId() == (int)$id) {
                $this->presenter->flashMessage($this->tf('user.deleted-callback.himself', $actionName), 'danger');
            } elseif ($id == 1) {
                $this->presenter->flashMessage($this->tf('user.deleted-callback.admin', $actionName), 'danger');
            } elseif ($this->userRole->isLessOrEqualsThan($userData['role']) && $this->userRole->isNot('admin')) {
                $this->presenter->flashMessage($this->tf('user.deleted-callback.same-role', $actionName), 'danger');
            } elseif (!$this->userManager->setDeleted((int)$id, $deleted === '1')) {
                $this->presenter->flashMessage($this->tf('user.deleted-callback.update-failed', $actionName), 'danger');
            }

            $this->presenter->redrawControl();
        }
    }
}
