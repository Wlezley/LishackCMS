<?php

declare(strict_types=1);

namespace App\Components\Admin;

interface IRedirectListFactory
{
    public function create(): RedirectList;
}
