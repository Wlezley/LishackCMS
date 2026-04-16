<?php

declare(strict_types=1);

namespace App\Components\Admin\CategoryForm;

interface ICategoryFormFactory
{
    public function create(): CategoryForm;
}
