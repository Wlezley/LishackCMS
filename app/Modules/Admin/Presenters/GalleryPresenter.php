<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presenters;

class GalleryPresenter extends SecuredPresenter
{
    public function __construct(
        // private MediaManager $mediaManager, // TODO: Implement
    ) {
        parent::__construct();
    }

    public function renderDefault(): void
    {
    }
}
