<?php

declare(strict_types=1);

namespace App\Components;

use App\Models\CategoryManager;

class Menu extends BaseControl
{
    public function __construct(
        protected CategoryManager $categoryManager
    ) {}

    public function render(string $template = "Menu"): void
    {
        // $this->template->menu = $this->categoryManager->getTree()[0]['items'];

        $this->template->setFile(__DIR__ . '/' . $template . '.latte');
        $this->template->render();
    }
}
