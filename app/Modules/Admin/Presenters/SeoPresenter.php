<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presenters;

class SeoPresenter extends SecuredPresenter
{
    public function renderDefault(): void
    {
        $this->template->title = 'SEO';
    }
}