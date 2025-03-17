<?php

declare(strict_types=1);

namespace App\Components\Admin;

interface IUserFormFactory
{
    public function create(): UserForm;
}
