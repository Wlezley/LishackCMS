<?php

declare(strict_types=1);

namespace App\Components\Admin\DatasetList;

use App\Components\BaseControl;
use App\Models\Dataset\DatasetManager;
use App\Modules\Admin\Presenters\DatasetPresenter;
use Nette\Utils\Json;
use Webmozart\Assert\Assert;

class DatasetList extends BaseControl
{
    /** @var int<0,max>|null $limit */
    private ?int $limit = null;
    private ?int $totalItems = null;

    public function __construct(
        private DatasetManager $datasetManager
    ) {
    }

    public function render(?int $limit = null): void
    {
        if ($limit === null) {
            $limit = (int)$this->c('PAGINATION_PAGE_ITEMS');
        }
        Assert::range($limit, 0, PHP_INT_MAX, 'Limit must be a positive integer.');
        $this->limit = $limit;

        $page = $this->param['page'] ?? 1;
        Assert::integer($page);
        $search = $this->param['search'] ?? null;
        Assert::nullOrString($search, 'Search must be a string or null.');
        $offset = ($page - 1) * $this->limit;
        Assert::range($offset, 0, PHP_INT_MAX, 'Offset must be a non-negative integer.');

        $this->totalItems = $this->datasetManager->getDatasetRepository()->getCount($search);
        $this->template->datasetList = $this->datasetManager->getDatasetRepository()->getList($this->limit, $offset, $search);

        $this->template->getJson = function ($id, $name) {
            // TODO: Fix empty modal on second call of deletion method
            return Json::encode([
                'id' => (string) $id,
                'modal' => [
                    'title' => $this->t('modal.title.confirm-delete'),
                    'body' => $this->tf('modal.body.delete-dataset', $name, $id),
                ],
            ]);
        };

        $this->getTemplate()->setFile(__DIR__ . '/DatasetList.latte');
        $this->getTemplate()->render();
    }

    public function handleDatalist(string $id): void
    {
        $this->presenter->redirect('Data:', [
            'datasetId' => $id,
        ]);
    }

    public function handleEdit(string $id): void
    {
        $this->presenter->redirect('Dataset:edit', [
            'id' => $id,
            'page' => $this->getPresenter()->getParameter('page', 1),
        ]);
    }

    // ##########################################
    // ###             PAGINATION             ###
    // ##########################################

    protected function createComponentPagination(): \App\Components\Pagination\Pagination
    {
        /** @var DatasetPresenter $presenter */
        $presenter = $this->getPresenter();

        $control = $presenter->paginationFactory->create();
        $control->setTranslator($presenter->translator);
        $control->setConfigManager($presenter->configManager);
        $control->setQueryParams($presenter->getHttpRequest()->getQuery());
        $control->setTotalItems($this->totalItems);
        $control->setCurrentPage((int) $presenter->getParameter('page', 1));

        if ($this->limit !== null) {
            $control->setItemsPerPage($this->limit);
        }

        return $control;
    }
}
