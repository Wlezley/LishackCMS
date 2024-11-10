<?php

declare(strict_types=1);

namespace App\Models;

use Nette\SmartObject;
use Nette\Database\Explorer;

class BaseModel
{
    use SmartObject;

    protected Explorer $db;

    protected array $data = [];

    public function __construct(Explorer $db)
    {
        $this->db = $db;
    }

    public function reload(): void
    {
        $this->data = [];
        $this->load();
    }
}
