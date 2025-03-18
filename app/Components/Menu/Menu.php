<?php

declare(strict_types=1);

namespace App\Components;

use App\Models\MenuManager;
use App\Models\UrlGenerator;

class Menu extends BaseControl
{
    public function __construct(protected MenuManager $menuManager, UrlGenerator $urlGenerator)
    {
        $this->menuManager->setUrlGenerator($urlGenerator);
        // $this->menuManager->setLang($this->lang);
    }

    public function render(string $template = "Menu"): void
    {
        $this->template->menu = $this->menuManager->getTree()[0]['items'];
        // $this->template->lang = $this->lang;

        $this->template->setFile(__DIR__ . '/' . $template . '.latte');
        $this->template->render();
    }
}
