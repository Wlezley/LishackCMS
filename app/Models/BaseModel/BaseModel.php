<?php

declare(strict_types=1);

namespace App\Models;

use Nette\SmartObject;
use Nette\Database\Explorer;

class BaseModel
{
    use SmartObject;

    // protected UrlGenerator $urlGenerator;

    protected array $data = [];

    protected string $lang;

    public function __construct(protected Explorer $db)
    {
        $this->lang = DEFAULT_LANG;

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

    public function setLang(string $lang): void
    {
        $this->lang = $lang;
    }

    public function getLang(): string
    {
        return $this->lang;
    }

    // public function setUrlGenerator(UrlGenerator $urlGenerator): void
    // {
    //     $this->urlGenerator = $urlGenerator;
    // }

    // public function getUrlGenerator(): UrlGenerator
    // {
    //     return $this->urlGenerator;
    // }
}
