<?php

declare(strict_types=1);

namespace App\Components\Admin;

interface IFileExplorerFactory
{
    public function create(): FileExplorer;
}
