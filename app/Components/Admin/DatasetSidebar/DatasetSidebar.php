<?php

declare(strict_types=1);

namespace App\Components\Admin\DatasetSidebar;

use App\Components\BaseControl;
use App\Service\Dataset\DatasetService;

class DatasetSidebar extends BaseControl
{
    public function __construct(
        private DatasetService $datasetService
    ) {
    }

    public function render(): void
    {
        $this->template->datasetList = $this->datasetService->getSidebarList();

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
