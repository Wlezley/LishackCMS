<?php

declare(strict_types=1);

namespace App\Components\Admin;

use App\Components\BaseControl;
use App\Models\Dataset\DatasetManager;
use App\Modules\Admin\Presenters\DataPresenter;
use Nette\Utils\Json;

class DatasetList extends BaseControl
{
    private ?int $limit = null;
    private ?int $totalItems = null;

    public function __construct(
        private DatasetManager $datasetManager
    ) {}

    public function render(?int $limit = null): void
    {
        $this->limit = $limit ?? (int)$this->c('PAGINATION_PAGE_ITEMS');

        $page = $this->param['page'] ?? 1;
        $search = $this->param['search'] ?? null;
        $offset = ($page - 1) * $this->limit;

        $this->totalItems = $this->datasetManager->getDatasetRepository()->getCount($search);
        $this->template->datasetList = $this->datasetManager->getDatasetRepository()->getList($this->limit, $offset, $search);

        $this->template->getJson = function($id, $name) {
            // TODO: Fix empty modal on second call of deletion method
            return Json::encode([
                'id' => (string) $id,
                // 'name' => (string) $name,
                'modal' => [
                    'title' => $this->t('modal.title.confirm-delete'),
                    'body' => $this->tf('modal.body.delete-dataset', $name, $id)
                ]
            ]);
        };

        $this->template->setFile(__DIR__ . '/DatasetList.latte');
        $this->template->render();
    }

    public function handleEdit(string $id): void
    {
        $this->presenter->redirect('Data:edit', [
            'id' => $id,
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
