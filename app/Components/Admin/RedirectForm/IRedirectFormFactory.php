<?php

declare(strict_types=1);

namespace App\Components\Admin\RedirectForm;

interface IRedirectFormFactory
{
    public function create(): RedirectForm;
}
