<?php

declare(strict_types=1);

namespace App\Components\Admin;

use App\Components\BaseControl;
use App\Modules\Admin\Presenters\TranslationPresenter;
use Nette\Utils\Json;
use Webmozart\Assert\Assert;

class TranslationList extends BaseControl
{
    private ?int $limit = null;
    private ?int $totalItems = null;

    public function render(string $lang, ?int $limit = null): void
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

        $this->totalItems = $this->translationManager->getCount($lang, $search);

        $this->template->getJson = function ($key) {
            return Json::encode([
                'key' => (string)$key,
                'modal' => [
                    'title' => $this->t('modal.title.confirm-delete'),
                    'body' => $this->tf('modal.body.delete-translation', $key),
                ],
            ]);
        };

        $this->template->lang = $lang;
        $this->template->translations = $this->translationManager->getList($lang, $this->limit, $offset, $search);

        $this->getTemplate()->setFile(__DIR__ . '/TranslationList.latte');
        $this->getTemplate()->render();
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
        $control->setTotalItems($this->totalItems);
        $control->setCurrentPage((int) $presenter->getParameter('page', 1));

        if ($this->limit !== null) {
            $control->setItemsPerPage($this->limit);
        }

        return $control;
    }
}
