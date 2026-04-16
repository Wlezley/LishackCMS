<?php

declare(strict_types=1);

namespace App\Components\Admin\ConfigSeoForm;

interface IConfigSeoFormFactory
{
    public function create(): ConfigSeoForm;
}
