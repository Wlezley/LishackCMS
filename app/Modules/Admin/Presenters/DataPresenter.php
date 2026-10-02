<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presenters;

use App\Components\Admin\DataEditor\DataEditor;
use App\Components\Admin\DataEditor\IDataEditorFactory;
use App\Components\Admin\DataList\DataList;
use App\Components\Admin\DataList\IDataListFactory;
use App\Service\Dataset\DatasetService;

class DataPresenter extends SecuredPresenter
{
    /** @var DatasetService @inject */
    public DatasetService $datasetService;

    /** @var IDataListFactory @inject */
    public IDataListFactory $dataList;

    /** @var IDataEditorFactory @inject */
    public IDataEditorFactory $dataEditor;

    public function renderDefault(int $datasetId = 0, int $page = 1, ?string $search = null): void
    {
        if (!$this->datasetService->loadDatasetById($datasetId, true)) {
            $this->flashMessage($this->tf('dataset.id.not-found', (int) $datasetId), 'danger');
            return;
        }

        $datasetName = $this->datasetService->getDataset()->getName();

        $this->template->title .= " ($datasetName)";
        $this->template->datasetId = $datasetId;
        $this->template->search = $search;
    }

    public function renderCreate(int $datasetId): void
    {
        if (!$this->datasetService->loadDatasetById($datasetId, true)) {
            $this->flashMessage($this->tf('dataset.id.not-found', (int) $datasetId), 'danger');
            $this->redirect(':default');
        }

        $datasetName = $this->datasetService->getDataset()->getName();

        $this->template->title .= " ($datasetName)";
    }

    public function renderEdit(int $datasetId, int $itemId): void
    {
        if (!$this->datasetService->loadDatasetById($datasetId, true)) {
            $this->flashMessage($this->tf('dataset.id.not-found', (int) $datasetId), 'danger');
            $this->redirect(':default');
        }

        $datasetName = $this->datasetService->getDataset()->getName();

        $this->template->title .= " ($datasetName / $itemId)";
    }

    public function handleDelete(): void
    {
        if (!$this->isAjax()) {
            $this->redirect('this');
        }

        $data = $this->getHttpRequest()->getPost();

        // TODO: Permission check

        $this->datasetService->deleteRow((int) $data['datasetId'], (int) $data['itemId']);

        $this->flashMessage("Řádek s ID {$data['itemId']} byl odstraněn.", 'info');
        $this->redirect(':default', [
            'datasetId' => $data['datasetId'],
        ]);
    }

    // ##########################################
    // ###             COMPONENTS             ###
    // ##########################################

    protected function createComponentDataList(): DataList
    {
        $control = $this->dataList->create();
        $control->setParam([
            'search' => $this->getParameter('search'),
            'page' => $this->getParameter('page'),
        ]);

        return $control;
    }

    protected function createComponentDataEditor(): DataEditor
    {
        $control = $this->dataEditor->create();

        $control->setDatasetService($this->datasetService);

        $control->setOrigin(
            $this->getParameter('itemId') ? $control::OriginEdit : $control::OriginCreate
        );

        $control->onSuccess = function (string $message, int $datasetId): void {
            $this->flashMessage($message, 'info');
            $this->redirect('Data:', ['datasetId' => $datasetId]);
        };

        $control->onError = function (string $message): void {
            $this->flashMessage($message, 'danger');
        };

        return $control;
    }
}
