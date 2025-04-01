<?php

declare(strict_types=1);

namespace App\Components\Admin;

use App\Components\BaseControl;
use App\Models\ArticleManager;
use App\Modules\Admin\Presenters\ArticlePresenter;
use Nette\Utils\Json;

class ArticleList extends BaseControl
{
    private ?int $limit = null;
    private ?int $totalItems = null;

    public function __construct(
        private ArticleManager $articleManager
    ) {}

    public function render(?int $limit = null): void
    {
        $this->limit = $limit ?? (int)$this->c('PAGINATION_PAGE_ITEMS');

        $page = $this->param['page'] ?? 1;
        $search = $this->param['search'] ?? null;
        $offset = ($page - 1) * $this->limit;

        $this->totalItems = $this->articleManager->getCount($search);
        $this->template->articleList = $this->articleManager->getList($this->limit, $offset, $search);

        $this->template->getJson = function($id, $title) {
            // TODO: Fix empty modal on second call of deletion method
            return Json::encode([
                'id' => (string)$id,
                'modal' => [
                    'title' => $this->t('modal.title.confirm-delete'),
                    'body' => $this->tf('modal.body.delete-article', $title)
                ]
            ]);
        };

        $this->template->setFile(__DIR__ . '/ArticleList.latte');
        $this->template->render();
    }

    public function handleEdit(string $id): void
    {
        $this->presenter->redirect('Article:edit', [
            'id' => $id,
            'page' => $this->getPresenter()->getParameter('page')
        ]);
    }

    // ##########################################
    // ###             PAGINATION             ###
    // ##########################################

    protected function createComponentPagination(): \App\Components\Pagination
    {
        /** @var ArticlePresenter $presenter */
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
