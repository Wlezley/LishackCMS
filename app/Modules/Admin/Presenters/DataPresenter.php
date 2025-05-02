<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presenters;

use App\Components\Admin\IDatasetEditorFactory;
use App\Models\Dataset\DatasetCreator;
use App\Components\Admin\IDataListFactory;
use App\Models\Dataset\DatasetManager;
use App\Models\Dataset\DatasetUpdater;

class DataPresenter extends SecuredPresenter
{
    /** @var DatasetCreator @inject */
    public DatasetCreator $datasetCreator;

    /** @var DatasetManager @inject */
    public DatasetManager $datasetManager;

    /** @var DatasetUpdater @inject */
    public DatasetUpdater $datasetUpdater;

    /** @var IDataListFactory @inject */
    public IDataListFactory $dataList;

    /** @var IDatasetEditorFactory @inject */
    public IDatasetEditorFactory $datasetEditor;

    public function renderDefault(int $datasetId, int $page = 1, ?string $search = null): void
    {
        $this->template->title .= " (dataset ID: $datasetId)";
        $this->template->datasetId = $datasetId;
        $this->template->search = $search;
    }

    public function renderCreate(): void
    {
    }

    public function renderEdit(int $id = 0): void
    {
        $this->template->title .= " ID: $id";

        if (!$this->datasetManager->loadDatasetById($id, true)) {
            $this->flashMessage($this->tf('dataset.id.not-found', (int) $id), 'danger');
            $this->redirect(':default');
        }
    }

    public function handleDelete(): void
    {
        if (!$this->isAjax()) {
            $this->redirect('this');
        }

        // $data = $this->getHttpRequest()->getPost();

        // TODO: Permission check

        // $this->datasetManager->deleteDataset((int) $data['datasetId']);
        // $this->flashMessage("Dataset ID: {$data['datasetId']} byl odstraněn.", 'info');
    }

    // ##########################################
    // ###             COMPONENTS             ###
    // ##########################################

    protected function createComponentDataList(): \App\Components\Admin\DataList
    {
        $control = $this->dataList->create();
        $control->setParam([
            'search' => $this->getParameter('search'),
            'page' => $this->getParameter('page'),
        ]);

        return $control;
    }

    protected function createComponentDatasetEditor(): \App\Components\Admin\DatasetEditor
    {
        $control = $this->datasetEditor->create();
        $id = $this->getParameter('id');

        $control->setDatasetCreator($this->datasetCreator); // TODO: Load it in Manager?
        $control->setDatasetManager($this->datasetManager);
        $control->setDatasetUpdater($this->datasetUpdater); // TODO: Load it in Manager?

        $control->setOrigin(
            $id ? $control::OriginEdit : $control::OriginCreate
        );

        $control->onSuccess = function(string $message): void {
            $this->flashMessage($message, 'info');
            $this->redirect('Data:');
        };

        $control->onError = function(string $message): void {
            $this->flashMessage($message, 'danger');
        };

        return $control;
    }
}
