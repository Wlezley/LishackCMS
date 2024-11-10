<?php

namespace App\Components;

use App\Models\Config;
use App\Models\UrlGenerator;
use Nette\Application\Request;
use Nette\Application\UI\Control;
use Nette\Http\IRequest;

class BaseControl extends Control
{
    protected string $title;
    protected string $lang;
    protected array $translations;

    protected Request $request;
    protected IRequest $httpRequest;

    protected Config $config;
    protected UrlGenerator $urlGenerator;

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setLang(string $lang): void
    {
        $this->lang = $lang;
    }

    public function getLang(): string
    {
        return $this->lang;
    }

    public function setTranslations(array $translations): void
    {
        $this->translations = $translations;
    }

    public function getTranslations(): array
    {
        return $this->translations;
    }

    public function setRequest(Request $request): void
    {
        $this->request = $request;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function setHttpRequest(IRequest $httpRequest): void
    {
        $this->httpRequest = $httpRequest;
    }

    public function getHttpRequest(): IRequest
    {
        return $this->httpRequest;
    }

    public function setConfig(Config $config): void
    {
        $this->config = $config;
    }

    public function getConfig(): Config
    {
        return $this->config;
    }

    public function setUrlGenerator(UrlGenerator $urlGenerator): void
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function getUrlGenerator(): UrlGenerator
    {
        return $this->urlGenerator;
    }
}
