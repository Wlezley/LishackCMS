<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presenters;

use App\Components\Admin\IDatasetListFactory;
use App\Models\Dataset\DatasetManager;

class DataPresenter extends SecuredPresenter
{
    /** @var DatasetManager @inject */
    public DatasetManager $datasetManager;

    /** @var IDatasetListFactory @inject */
    public IDatasetListFactory $datasetList;

    public function renderDefault(int $page = 1, ?string $search = null): void
    {
        $this->template->search = $search;
    }

    public function renderCreate(): void
    {
    }

    public function renderEdit(int $id = 0): void
    {
        $this->template->title .= " ID: $id";
        $this->template->containerId = $id;

        if (!$this->datasetManager->loadDatasetById($id)) {
            $this->flashMessage($this->tf('dataset.id.not-found', (int) $id));
            $this->redirect(':default');
        }
    }

    public function actionDelete(int $id): void
    {
        // TODO: Permission check

        $this->datasetManager->deleteDataset((int) $id);

        $this->flashMessage("Dataset ID: $id byl odstraněn.", 'info');
        $this->redirect('Data:');
    }

    public function handleDelete(): void
    {
        if (!$this->isAjax()) {
            $this->redirect('this');
        }

        $data = $this->getHttpRequest()->getPost();

        // TODO: Permission check

        $this->datasetManager->deleteDataset((int) $data['id']);
        $this->flashMessage("Dataset ID: {$data['id']} byl odstraněn.", 'info');
    }

    // ##########################################
    // ###             COMPONENTS             ###
    // ##########################################

    protected function createComponentDatasetList(): \App\Components\Admin\DatasetList
    {
        $control = $this->datasetList->create();
        $control->setParam([
            'search' => $this->getParameter('search'),
            'page' => $this->getParameter('page'),
        ]);

        return $control;
    }

}
