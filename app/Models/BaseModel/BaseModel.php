<?php

declare(strict_types=1);

namespace App\Models;

use Nette;
use Nette\SmartObject;
use Nette\Database\Explorer;

class BaseModel
{
    use SmartObject;

    protected Explorer $db;

    public function __construct(Explorer $db)
    {
        $this->db = $db;
    }
}
