<?php

declare(strict_types=1);

namespace App\Components;

class AdminButton extends BaseControl
{
    protected string $adminUrl;

    public function render(): void
    {
        $this->template->adminUrl = $this->adminUrl;
        $this->template->setFile(__DIR__ . '/AdminButton.latte');
        $this->template->render();
    }

    public function setadminUrl(string $adminUrl): void
    {
        $this->adminUrl = $adminUrl;
    }
}
