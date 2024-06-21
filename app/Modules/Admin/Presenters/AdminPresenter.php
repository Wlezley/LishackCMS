<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presenters;


class AdminPresenter extends SecuredPresenter
{
    public function renderDefault()
    {
        $this->template->user = $this->user->identity->data;
    }
}
