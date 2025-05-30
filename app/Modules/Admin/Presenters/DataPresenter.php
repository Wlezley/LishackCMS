<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presenters;

use App\Components\Admin\IDataListFactory;
use App\Components\Admin\IDataEditorFactory;
use App\Models\Dataset\DatasetManager;

class DataPresenter extends SecuredPresenter
{
    /** @var DatasetManager @inject */
    public DatasetManager $datasetManager;

    /** @var IDataListFactory @inject */
    public IDataListFactory $dataList;

    /** @var IDataEditorFactory @inject */
    public IDataEditorFactory $dataEditor;

    public function renderDefault(int $datasetId = 0, int $page = 1, ?string $search = null): void
    {
        if (!$this->datasetManager->loadDatasetById($datasetId, true)) {
            $this->flashMessage($this->tf('dataset.id.not-found', (int) $datasetId), 'danger');
            return;
        }

        $datasetName = $this->datasetManager->getDataset()->name;

        $this->template->title .= " ($datasetName)";
        $this->template->datasetId = $datasetId;
        $this->template->search = $search;
    }

    public function renderCreate(int $datasetId): void
    {
        if (!$this->datasetManager->loadDatasetById($datasetId, true)) {
            $this->flashMessage($this->tf('dataset.id.not-found', (int) $datasetId), 'danger');
            $this->redirect(':default');
        }

        $datasetName = $this->datasetManager->getDataset()->name;

        $this->template->title .= " ($datasetName)";
    }

    public function renderEdit(int $datasetId, int $itemId): void
    {
        if (!$this->datasetManager->loadDatasetById($datasetId, true)) {
            $this->flashMessage($this->tf('dataset.id.not-found', (int) $datasetId), 'danger');
            $this->redirect(':default');
        }

        $datasetName = $this->datasetManager->getDataset()->name;

        $this->template->title .= " ($datasetName / $itemId)";
    }

    public function handleDelete(): void
    {
        if (!$this->isAjax()) {
            $this->redirect('this');
        }

        $data = $this->getHttpRequest()->getPost();

        // TODO: Permission check

        $this->datasetManager->deleteRow((int) $data['datasetId'], (int) $data['itemId']);

        $this->flashMessage("Řádek s ID {$data['itemId']} byl odstraněn.", 'info');
        $this->redirect(':default', [
            'datasetId' => $data['datasetId']
        ]);
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

    protected function createComponentDataEditor(): \App\Components\Admin\DataEditor
    {
        $control = $this->dataEditor->create();

        $control->setDatasetManager($this->datasetManager);

        $control->setOrigin(
            $this->getParameter('itemId') ? $control::OriginEdit : $control::OriginCreate
        );

        $control->onSuccess = function(string $message, int $datasetId): void {
            $this->flashMessage($message, 'info');
            $this->redirect('Data:', ['datasetId' => $datasetId]);
        };

        $control->onError = function(string $message): void {
            $this->flashMessage($message, 'danger');
        };

        return $control;
    }
}
