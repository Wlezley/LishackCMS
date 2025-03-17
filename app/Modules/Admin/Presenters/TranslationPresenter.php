<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presenters;

class TranslationPresenter extends SecuredPresenter
{
    public function renderDefault(int $page = 1, ?string $lang = null, ?string $search = null): void
    {
        $this->template->title = 'Jazykový překlad';

        $lang = $lang ?? DEFAULT_LANG;
        $limit = 5;
        $offset = ($page - 1) * $limit;

        $this->template->translations = $this->translationManager->getList(
            $lang,
            $limit,
            $offset,
            $search
        );

        $totalCount = $this->translationManager->getCount($lang, $search);
        $totalPages = (int) ceil($totalCount / $limit);

        $this->template->page = $page;
        $this->template->search = $search;
        $this->template->totalCount = $totalCount;
        $this->template->totalPages = $totalPages;
    }

    public function renderCreate(): void
    {
        $this->template->title = 'Nový jazykový překlad';
    }
}
