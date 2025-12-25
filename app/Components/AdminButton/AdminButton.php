<?php

declare(strict_types=1);

namespace App\Components\AdminButton;

use App\Components\BaseControl;
use Webmozart\Assert\Assert;

class AdminButton extends BaseControl
{
    protected ?string $templatePath = __DIR__ . '/AdminButton.latte';

    protected string $adminUrl;

    public function render(): void
    {
        $this->template->adminUrl = $this->adminUrl;

        Assert::notNull($this->templatePath);

        $this->getTemplate()->setFile($this->templatePath);
        $this->getTemplate()->render();
    }

    public function setAdminUrl(string $adminUrl): void
    {
        $this->adminUrl = $adminUrl;
    }
}
