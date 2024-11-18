<?php

namespace App\Components;

use App\Models\UrlGenerator;

class Menu extends BaseControl
{
    public function __construct(protected \App\Models\Menu $menu, UrlGenerator $urlGenerator)
    {
        $this->menu->setUrlGenerator($urlGenerator);
        $this->menu->setLang($this->lang);
    }

    public function render(string $template = "Menu"): void
    {
        $this->template->menu = $this->menu->getMenuTree()[0]['items'];
        $this->template->lang = $this->lang;

        $this->template->setFile(__DIR__ . '/' . $template . '.latte');
        $this->template->render();
    }
}
