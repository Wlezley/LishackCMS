<?php

declare(strict_types=1);

namespace App\Components\Admin\DatasetList;

interface IDatasetListFactory
{
    public function create(): DatasetList;
}
