<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presenters;

use App\Components\Admin\ITranslationFormFactory;

class TranslationPresenter extends SecuredPresenter
{
    /** @var ITranslationFormFactory @inject */
    public ITranslationFormFactory $translationForm;

    public function renderDefault(int $page = 1, ?string $lang = null, ?string $search = null): void
    {
        $lang = $lang ?? $this->translationManager->getLanguageService()->getDefaultLang(DEFAULT_LANG);

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

        $this->template->lang = $lang;
        $this->template->totalItems = $totalItems;
        $this->template->search = $search;
    }

    public function renderCreate(): void
    {
        $this->template->title = 'Nový jazykový překlad';
    }

    public function renderEdit(string $key): void
    {
        $this->template->title = "Editace překladu '$key'";
    }

    // ##########################################
    // ###             COMPONENTS             ###
    // ##########################################

    protected function createComponentTranslationForm(): \App\Components\Admin\TranslationForm
    {
        $form = $this->translationForm->create();
        $key = $this->getParameter('key');

        if ($key) {
            $form->setOrigin($form::OriginEdit);

            $param['key'] = $key;
            foreach ($this->translationManager->getTextListByKey($key) as $key => $text) {
                $param["text_$key"] = $text;
            }

            $form->setParam($param);
        } else {
            $form->setOrigin($form::OriginCreate);
            $form->setQueryParams($this->getHttpRequest()->getQuery());
            $form->setParam($this->getHttpRequest()->getPost('param'));
        }

        $form->setLanguageList($this->translationManager->getLanguageService()->getList(false));

        $form->onSuccess = function(string $message): void {
            $this->flashMessage($message, 'info');
            $this->redirect('Translation:default');
        };

        $form->onError = function(string $message): void {
            $this->flashMessage($message, 'danger');
            // $this->redirect('this');
        };

        return $form;
    }
}
