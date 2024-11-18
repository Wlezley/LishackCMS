<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presenters;

class _SecuredPresenter extends _BasePresenter
{
    public function startup(): void
    {
        parent::startup();

        if (!$this->user->isLoggedIn() && $this->presenter->getName() !== "Admin:Sign") {
            $this->redirect("Sign:in");
        }

        if ($this->user->isLoggedIn()) {
            $userData = $this->db->table(\App\Models\UserManager::TABLE_NAME)->select('deleted, enabled')->where([
                'id' => $this->user->getId()
            ])->fetch();

            if (!$userData || $userData->deleted == 1 || $userData->enabled != 1) {
                $this->user->logout();
                $this->flashMessage('Uživatel byl odhlášen', 'danger');
                $this->redirect("Sign:in");
            }
        }
    }

    public function afterRender(): void
    {
        parent::afterRender();

        $this->template->userData = $this->user->identity->data;
    }
}
