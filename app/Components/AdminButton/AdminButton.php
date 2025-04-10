<?php

declare(strict_types=1);

namespace App\Components;

class AdminButton extends BaseControl
{
    /** @var string $templatePath */
    protected ?string $templatePath = __DIR__ . '/AdminButton.latte';

    /** @var string $adminUrl */
    protected string $adminUrl;

    public function render(): void
    {
        $this->template->adminUrl = $this->adminUrl;

        $this->template->setFile($this->templatePath);
        $this->template->render();
    }

    public function setadminUrl(string $adminUrl): void
    {
        $this->adminUrl = $adminUrl;
    }
}
