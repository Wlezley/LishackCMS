<?php

declare(strict_types=1);

namespace App\Components;

use Nette\Application\UI\Control;

class BaseControl extends Control
{
    protected string $lang = DEFAULT_LANG;

    /** @var array<string,string> $cmsConfig */
    protected array $cmsConfig = [];

    /** @var null|array<string,string> $param */
    protected ?array $param = [];

    public function setLang(string $lang): void
    {
        $this->lang = $lang;
    }

    public function getLang(): string
    {
        return $this->lang;
    }

    /** @param array<string,string> $cmsConfig */
    public function setCmsConfig(array $cmsConfig): void
    {
        $this->cmsConfig = $cmsConfig;
    }

    /** @return array<string,string> */
    public function getCmsConfig(): array
    {
        return $this->cmsConfig;
    }

    /** @param null|array<string,string> $param */
    public function setParam(?array $param): void
    {
        $this->param = $param === null ? [] : $param;
    }

    /** @return array<string,string> */
    public function getParam(): array
    {
        return $this->param;
    }
}
