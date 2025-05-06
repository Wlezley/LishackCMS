<?php

declare(strict_types=1);

namespace App\Components\Admin;

use App\Components\BaseControl;
use App\Models\Dataset\DatasetManager;
use App\Modules\Admin\Presenters\DataPresenter;
use Nette\Utils\Json;

class DataList extends BaseControl
{
    private ?int $limit = null;
    private ?int $totalItems = null;

    public function __construct(
        private DatasetManager $datasetManager
    ) {}

    public function render(int $datasetId, ?int $limit = null): void
    {
        $this->limit = $limit ?? (int)$this->c('PAGINATION_PAGE_ITEMS');

        $page = $this->param['page'] ?? 1;
        $search = $this->param['search'] ?? null;
        $offset = ($page - 1) * $this->limit;

        $this->datasetManager->loadDatasetById($datasetId);
        $this->totalItems = $this->datasetManager->getDataRepository()->getCount($datasetId, $search);
        $this->template->dataList = $this->datasetManager->getDataRepository()->getList($datasetId, $this->limit, $offset, $search);
        $this->template->listColumns = $this->datasetManager->getListedColumns();
        $this->template->datasetId = $datasetId;

        $this->template->getJson = function($datasetId, $itemId) {
            // TODO: Fix empty modal on second call of deletion method
            return Json::encode([
                'itemId' => (string) $itemId,
                'datasetId' => (int) $datasetId,
                'modal' => [
                    'title' => $this->t('modal.title.confirm-delete'),
                    'body' => $this->tf('modal.body.delete-dataset-data', $itemId)
                ]
            ]);
        };

        $this->template->setFile(__DIR__ . '/DataList.latte');
        $this->template->render();
    }

    public function handleEdit(string $itemId): void
    {
        $this->presenter->redirect('Data:edit', [
            'datasetId' => $this->getPresenter()->getParameter('datasetId'),
            'itemId' => $itemId,
            'page' => $this->getPresenter()->getParameter('page', 1)
        ]);
    }

    // ##########################################
    // ###             PAGINATION             ###
    // ##########################################

    protected function createComponentPagination(): \App\Components\Pagination
    {
        /** @var DataPresenter $presenter */
        $presenter = $this->getPresenter();

        $control = $presenter->paginationFactory->create();
        $control->setTranslationManager($presenter->translationManager);
        $control->setConfigManager($presenter->configManager);
        $control->setQueryParams($presenter->getHttpRequest()->getQuery());
        $control->setItemsPerPage($this->limit);
        $control->setTotalItems($this->totalItems);
        $control->setCurrentPage((int) $presenter->getParameter('page', 1));

        return $control;
    }
}
