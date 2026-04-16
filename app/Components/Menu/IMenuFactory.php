<?php

declare(strict_types=1);

namespace App\Components\Menu;

interface IMenuFactory
{
    public function create(): Menu;
}
