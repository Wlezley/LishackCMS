<?php

declare(strict_types=1);

namespace App\Components\Admin;

interface IConfigWebsiteFormFactory
{
    public function create(): ConfigWebsiteForm;
}
