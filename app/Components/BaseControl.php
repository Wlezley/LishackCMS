<?php

namespace App\Components;

use Nette\Application\UI\Control;

class BaseControl extends Control
{
    protected string $lang = DEFAULT_LANG;
    protected array $cmsConfig = [];
    protected ?array $param = [];

    public function setLang(string $lang): void
    {
        $this->lang = $lang;
    }

    public function getLang(): string
    {
        return $this->lang;
    }

    public function setCmsConfig(array $cmsConfig): void
    {
        $this->cmsConfig = $cmsConfig;
    }

    public function getCmsConfig(): array
    {
        return $this->cmsConfig;
    }

    public function setParam(?array $param): void
    {
        $this->param = $param;
    }

    public function getParam(): array
    {
        return $this->param;
    }
}
