<?php

declare(strict_types=1);

namespace App\Components\Admin\DatasetSidebar;

use App\Components\BaseControl;
use App\Models\Dataset\DatasetManager;

class DatasetSidebar extends BaseControl
{
    public function __construct(
        private DatasetManager $datasetManager
    ) {
    }

    public function render(): void
    {
        $this->template->datasetList = $this->datasetManager->getDatasetRepository()->getSidebarList();

        $this->getTemplate()->setFile(__DIR__ . '/DatasetSidebar.latte');
        $this->getTemplate()->render();
    }

    public function handleEdit(string $datasetId): void
    {
        $this->presenter->redirect('Data:default', [
            'datasetId' => $datasetId,
        ]);
    }
}
