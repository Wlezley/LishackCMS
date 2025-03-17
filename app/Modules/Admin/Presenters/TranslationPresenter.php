<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presenters;

class TranslationPresenter extends SecuredPresenter
{
    public function renderDefault(int $page = 1, ?string $lang = null, ?string $search = null): void
    {
        $lang = $lang ?? DEFAULT_LANG;

        $langData = $this->translationManager->getLanguageService()->getLanguage($lang);
        if ($langData === null) {
            $this->redirect('Translation:');
        }

        $this->template->title = 'Jazykový překlad (' . $langData['name'] . ')';

        $limit = 10;
        $offset = ($page - 1) * $limit;

        $this->template->translations = $this->translationManager->getList(
            $lang,
            $limit,
            $offset,
            $search
        );

        $totalItems = $this->translationManager->getCount($lang, $search);
        $this->setPagination($limit, $totalItems);

        $this->template->lang = ($lang == DEFAULT_LANG) ? '' : $lang;
        $this->template->totalItems = $totalItems;
        $this->template->search = $search;
    }

    public function renderCreate(): void
    {
        $this->template->title = 'Nový jazykový překlad';
    }
}
