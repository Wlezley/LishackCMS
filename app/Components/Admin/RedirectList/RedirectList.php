<?php

declare(strict_types=1);

namespace App\Components\Admin;

use App\Components\BaseControl;
use App\Models\RedirectManager;
use App\Modules\Admin\Presenters\RedirectPresenter;
use Nette\Utils\Json;

class RedirectList extends BaseControl
{
    private ?int $limit = null;
    private ?int $totalItems = null;

    public function __construct(
        private RedirectManager $redirectManager
    ) {}

    public function render(int $limit = 10): void
    {
        $this->limit = $limit;

        $page = $this->param['page'] ?? 1;
        $search = $this->param['search'] ?? null;
        $offset = ($page - 1) * $this->limit;

        $this->totalItems = $this->redirectManager->getCount($search);
        $this->template->redirectList = $this->redirectManager->getList($this->limit, $offset, $search);

        $this->template->getJson = function($source) {
            return Json::encode([
                'source' => (string)$source,
                'modal' => [
                    'title' => 'Potvrzení o smazání',
                    'body' => 'Opravdu chcete přesměrování <strong>' . $source . '</strong> smazat?'
                ]
            ]);
        };

        $this->template->setFile(__DIR__ . '/RedirectList.latte');
        $this->template->render();
    }

    public function handleEdit(string $source): void
    {
        $this->presenter->redirect('Redirect:edit', ['source' => $source]);
    }

    // ##########################################
    // ###             PAGINATION             ###
    // ##########################################

    protected function createComponentPagination(): \App\Components\Pagination
    {
        /** @var RedirectPresenter $presenter */
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
