<?php

declare(strict_types=1);

namespace App\Components\AdminButton;

interface IAdminButtonFactory
{
    public function create(): AdminButton;
}
