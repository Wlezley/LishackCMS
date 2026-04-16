<?php

declare(strict_types=1);

namespace App\Components\Admin\DataList;

interface IDataListFactory
{
    public function create(): DataList;
}
