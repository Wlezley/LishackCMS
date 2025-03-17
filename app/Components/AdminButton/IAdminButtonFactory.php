<?php

declare(strict_types=1);

namespace App\Components;

interface IAdminButtonFactory
{
    public function create(): AdminButton;
}
