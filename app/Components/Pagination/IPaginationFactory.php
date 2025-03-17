<?php

declare(strict_types=1);

namespace App\Components;

interface IPaginationFactory
{
    public function create(): Pagination;
}
