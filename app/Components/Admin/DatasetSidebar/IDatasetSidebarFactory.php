<?php

declare(strict_types=1);

namespace App\Components\Admin\DatasetSidebar;

interface IDatasetSidebarFactory
{
    public function create(): DatasetSidebar;
}
