<?php

declare(strict_types=1);

namespace App\Components\Admin\UserForm;

interface IUserFormFactory
{
    public function create(): UserForm;
}
