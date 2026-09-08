<?php

declare(strict_types=1);

namespace App\Components\Admin\RedirectList;

use App\Components\BaseControl;
use App\Entity\Redirect\RedirectRepositoryInterface;
use App\Modules\Admin\Presenters\RedirectPresenter;
use Nette\Utils\Json;
use Webmozart\Assert\Assert;

class RedirectList extends BaseControl
{
    private ?int $limit = null;
    private ?int $totalItems = null;

    public function __construct(
        private RedirectRepositoryInterface $redirectRepository,
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
        Assert::integer($page, 'Page must be an integer.');
        $search = $this->param['search'] ?? null;
        Assert::nullOrString($search, 'Search must be a string or null.');
        $offset = ($page - 1) * $this->limit;
        Assert::range($offset, 0, PHP_INT_MAX, 'Offset must be a non-negative integer.');

        $this->totalItems = $this->redirectRepository->countBySearch($search);
        $this->template->redirectList = $this->redirectRepository->findPaginated(
            limit: $this->limit,
            offset: $offset,
            search: $search
        );

        $this->template->getJson = function ($id, $source) {
            // TODO: Fix empty modal on second call of deletion method
            return Json::encode([
                'id' => (string)$id,
                // 'source' => (string)$source,
                'modal' => [
                    'title' => $this->t('modal.title.confirm-delete'),
                    'body' => $this->tf('modal.body.delete-redirect', $source),
                ],
            ]);
        };

        $this->getTemplate()->setFile(__DIR__ . '/RedirectList.latte');
        $this->getTemplate()->render();
    }

    public function handleEdit(string $id): void
    {
        $this->presenter->redirect('Redirect:edit', [
            'id' => $id,
            'page' => $this->getPresenter()->getParameter('page', 1),
        ]);
    }

    // ##########################################
    // ###             PAGINATION             ###
    // ##########################################

    protected function createComponentPagination(): \App\Components\Pagination\Pagination
    {
        /** @var RedirectPresenter $presenter */
        $presenter = $this->getPresenter();

        $control = $presenter->paginationFactory->create();
        $control->setTranslationService($presenter->translationService);
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
