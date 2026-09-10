<?php

declare(strict_types=1);

namespace App\Components\Admin\TranslationList;

use App\Components\BaseControl;
use App\Dto\Localization\TranslationCollectionDto;
use App\Entity\Language\Language;
use App\Modules\Admin\Presenters\TranslationPresenter;
use Nette\Utils\Json;
use Webmozart\Assert\Assert;

class TranslationList extends BaseControl
{
    private ?int $limit = null;
    private ?int $totalItems = null;

    public function render(Language $language, ?int $limit = null): void
    {
        if ($limit === null) {
            $limit = (int)$this->c('PAGINATION_PAGE_ITEMS');
        }
        Assert::range($limit, 0, PHP_INT_MAX, 'Limit must be positive integer.');
        $this->limit = $limit;

        $page = $this->getIntParam('page') ?? 1;
        $search = $this->getStringParam('search');
        $offset = ($page - 1) * $this->limit;
        Assert::range($offset, 0, PHP_INT_MAX, 'Offset must be positive integer.');

        $this->totalItems = $this->translationService->getCount($language, $search);

        $this->template->getJson = function (string $translationKey) {
            return Json::encode([
                'translationKey' => $translationKey,
                'modal' => [
                    'title' => $this->t('modal.title.confirm-delete'),
                    'body' => $this->tf('modal.body.delete-translation', $translationKey),
                ],
            ]);
        };

        $this->template->language = $language;
        $this->template->translations = TranslationCollectionDto::fromEntities(
            $this->translationService->getList($language, $search, $this->limit, $offset)
        )->toArray();

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

    protected function createComponentPagination(): \App\Components\Pagination\Pagination
    {
        /** @var TranslationPresenter $presenter */
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
