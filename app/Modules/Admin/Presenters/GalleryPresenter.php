<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presenters;

use App\Models\Media\MediaManager;

class GalleryPresenter extends SecuredPresenter
{
    public function __construct(
        // private MediaManager $mediaManager
    ) {}

    public function renderDefault(): void
    {
    }
}
