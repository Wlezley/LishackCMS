<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presenters;

class AdminPresenter extends _SecuredPresenter
{
    public function renderDefault()
    {
        $this->flashMessage('Flash message test', 'info');

        $this->template->title = 'Přehled';
    }
}
