<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presenters;

use App\Components\Admin\IUserFormFactory;
use App\Models\UserRole;

class SecuredPresenter extends BasePresenter
{
    /** @var IUserFormFactory @inject */
    public $userForm;

    protected UserRole $userRole;

    public function startup(): void
    {
        parent::startup();

        if (!$this->user->isLoggedIn() && $this->presenter->getName() !== 'Admin:Sign') {
            if ($this->isAjax()) {
                $this->flashMessage('Přístup odepřen: Uživatel se odhlásil', 'danger');
            }

            $this->redirect('Sign:in');
        }

        if ($this->user->isLoggedIn()) {
            $userData = $this->db->table(\App\Models\UserManager::TABLE_NAME)->select('deleted, enabled, role')->where([
                'id' => $this->user->getId()
            ])->fetch();

            if (!$userData || $userData['deleted'] == 1 || $userData['enabled'] != 1) {
                $this->user->logout(true);
                $this->flashMessage('Uživatel byl odhlášen', 'danger');
                $this->redirect('Sign:in');
            }

            if ($this->user->getIdentity()->getData()['role'] !== $userData['role']) {
                $this->user->logout(true);
                $this->flashMessage('Uživatel byl odhlášen: Změna role', 'danger');
                $this->redirect('Sign:in');
            }
        }

        $this->userRole = new UserRole($this->user);
    }

    public function afterRender(): void
    {
        parent::afterRender();

        $this->template->userData = $this->user->identity->getData();
    }
}
