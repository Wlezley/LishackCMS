<?php

declare(strict_types=1);

namespace App\Components\Admin;

use App\Components\BaseControl;
use App\Models\Dataset\DatasetManager;

class DatasetSidebar extends BaseControl
{
    public function __construct(
        private DatasetManager $datasetManager
    ) {}

    public function render(): void
    {
        $this->template->datasetList = $this->datasetManager->getDatasetRepository()->getSidebarList();

        $this->template->setFile(__DIR__ . '/DatasetSidebar.latte');
        $this->template->render();
    }

    public function handleEdit(string $datasetId): void
    {
        $this->presenter->redirect('Data:default', [
            'datasetId' => $datasetId
        ]);
    }
}
