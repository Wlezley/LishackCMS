<?php

declare(strict_types=1);

namespace App\Components\Pagination;

interface IPaginationFactory
{
    public function create(): Pagination;
}
