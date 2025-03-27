<?php

declare(strict_types=1);

namespace App\Components\Admin;

interface ICategoryFormFactory
{
    public function create(): CategoryForm;
}
