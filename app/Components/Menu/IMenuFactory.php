<?php

declare(strict_types=1);

namespace App\Components;

interface IMenuFactory
{
    public function create(): Menu;
}
