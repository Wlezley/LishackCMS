<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presenters;

class AdminPresenter extends SecuredPresenter
{
    public function renderDefault(): void
    {
        $this->flashMessage('Flash message test', 'info');

        $this->template->title = 'Přehled';
    }
}
