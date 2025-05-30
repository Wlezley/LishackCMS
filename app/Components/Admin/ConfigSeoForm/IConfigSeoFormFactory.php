<?php

declare(strict_types=1);

namespace App\Components\Admin;

interface IConfigSeoFormFactory
{
    public function create(): ConfigSeoForm;
}
