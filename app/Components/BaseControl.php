<?php

namespace App\Components;

use Nette\Application\UI\Control;

class BaseControl extends Control
{
    protected string $lang = '';

    public function setLang(string $lang): void
    {
        $this->lang = $lang;
    }

    public function getLang(): string
    {
        return $this->lang;
    }
}
