<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presenters;

use App\Components\Admin\IUserFormFactory;

class SecuredPresenter extends BasePresenter
{
    /** @var IUserFormFactory @inject */
    public $userForm;

    public function startup(): void
    {
        parent::startup();

        if (!$this->user->isLoggedIn() && $this->presenter->getName() !== 'Admin:Sign') {
            $this->redirect('Sign:in');
        }

        if ($this->user->isLoggedIn()) {
            $userData = $this->db->table(\App\Models\UserManager::TABLE_NAME)->select('deleted, enabled')->where([
                'id' => $this->user->getId()
            ])->fetch();

            if (!$userData || $userData['deleted'] == 1 || $userData['enabled'] != 1) {
                $this->user->logout();
                $this->flashMessage('Uživatel byl odhlášen', 'danger');
                $this->redirect('Sign:in');
            }
        }
    }

    public function afterRender(): void
    {
        parent::afterRender();

        $this->template->userData = $this->user->identity->getData();
    }

    protected function createComponentUserForm(): \App\Components\Admin\UserForm
    {
        $form = $this->userForm->create();
        $form->setCmsConfig($this->cmsConfig);
        $form->setParam($this->getHttpRequest()->getPost('param'));

        // $form->onSuccess[] = function (): void {
        //     $this->redirect('this#form', $this->getParameters());
        // };

        return $form;
    }
}