<?php

declare(strict_types=1);

namespace App\Components\Admin;

interface IRedirectFormFactory
{
    public function create(): RedirectForm;
}
