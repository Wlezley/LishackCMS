<?php

declare(strict_types=1);

namespace App\Models;

use Nette\SmartObject;
use Nette\Database\Explorer;

class BaseModel
{
    use SmartObject;

    protected array $data = [];

    public function __construct(protected Explorer $db)
    {
        $this->load();
    }

    public function load(): void
    {
    }

    public function reload(): void
    {
        $this->data = [];
        $this->load();
    }
}
