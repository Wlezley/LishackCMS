<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presenters;

class StringsPresenter extends SecuredPresenter
{
    public function renderDefault(): void
    {
        $this->template->title = 'Textové definice';
    }
}
