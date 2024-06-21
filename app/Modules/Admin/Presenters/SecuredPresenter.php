<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presenters;


class SecuredPresenter extends _BasePresenter
{
    public function startup(): void
    {
        parent::startup();

        if (!$this->user->isLoggedIn() && $this->presenter->getName() !== "Admin:Sign") {
            $this->redirect("Sign:in");
        }
    }
}
