<?php

declare(strict_types=1);

namespace App\Components\Menu;

use App\Components\BaseControl;
use App\Models\Category\CategoryManager;

class Menu extends BaseControl
{
    public function __construct(
        protected CategoryManager $categoryManager
    ) {
    }

    public function render(?int $activeCategory = null, string $template = 'Menu'): void
    {
        $activeCategory = $activeCategory ?? CategoryManager::MAIN_CATEGORY_ID;

        $this->template->activeCategory = $activeCategory;
        $this->template->activeList = $this->categoryManager->getActiveList($activeCategory);
        $this->template->menuItems = $this->categoryManager->getTree()[0]['items'];

        $this->getTemplate()->setFile(__DIR__ . '/' . $template . '.latte');
        $this->getTemplate()->render();
    }
}
