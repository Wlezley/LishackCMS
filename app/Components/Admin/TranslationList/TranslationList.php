<?php

declare(strict_types=1);

namespace App\Components\Admin;

use App\Components\BaseControl;
use App\Modules\Admin\Presenters\TranslationPresenter;
use Nette\Utils\Json;

class TranslationList extends BaseControl
{
    private ?int $limit = null;
    private ?int $totalItems = null;

    public function render(string $lang, ?int $limit = null): void
    {
        $this->limit = $limit ?? (int)$this->c('PAGINATION_PAGE_ITEMS');

        $page = $this->param['page'] ?? 1;
        $search = $this->param['search'] ?? null;
        $offset = ($page - 1) * $this->limit;

        $this->totalItems = $this->translationManager->getCount($lang, $search);

        $this->template->getJson = function($key) {
            return Json::encode([
                'key' => (string)$key,
                'modal' => [
                    'title' => 'Potvrzení o smazání',
                    'body' => 'Opravdu chcete překlad <strong>' . $key . '</strong> smazat?'
                ]
            ]);
        };

        $this->template->lang = $lang;
        $this->template->translations = $this->translationManager->getList($lang, $this->limit, $offset, $search);

        $this->template->setFile(__DIR__ . '/TranslationList.latte');
        $this->template->render();
    }

    public function handleEdit(string $key, ?string $lang = null): void
    {
        $this->presenter->redirect('Translation:edit', ['key' => $key, 'lang' => $lang]);
    }

    // ##########################################
    // ###             PAGINATION             ###
    // ##########################################

    protected function createComponentPagination(): \App\Components\Pagination
    {
        /** @var TranslationPresenter $presenter */
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
