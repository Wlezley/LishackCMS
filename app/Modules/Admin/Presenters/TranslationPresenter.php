<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presenters;

use App\Components\Admin\ITranslationFormFactory;
use App\Components\Admin\ITranslationEditorFactory;
use App\Components\Admin\ITranslationListFactory;

class TranslationPresenter extends SecuredPresenter
{
    /** @var ITranslationListFactory @inject */
    public ITranslationListFactory $translationList;

    /** @var ITranslationFormFactory @inject */
    public ITranslationFormFactory $translationForm;

    /** @var ITranslationEditorFactory @inject */
    public ITranslationEditorFactory $translationEditor;

    public function renderDefault(int $page = 1, ?string $lang = null, ?string $search = null): void
    {
        $languageService = $this->translationManager->getLanguageService();
        $lang = $lang ?? $languageService->getDefaultLang($this->c('DEFAULT_LANG'));
        $langData = $languageService->getLanguage($lang);

        if ($langData === null) {
            $this->redirect('Translation:');
        }

        $this->template->title .= ' - ' . $langData['name'] . ($langData['default'] ? ' (' . $this->t('default') . ')' : '');

        $this->template->lang = $lang;
        $this->template->langList = $languageService->getList(false);
        $this->template->search = $search;
    }

    public function renderEditor(string $lang = ''): void
    {
        $languageService = $this->translationManager->getLanguageService();

        $langList = $languageService->getList(false);
        $defaultLang = $languageService->getDefaultLang($this->c('DEFAULT_LANG'));

        if (empty($lang) || $lang == $defaultLang || !array_key_exists($lang, $langList)) {
            $redirLang = $languageService->getSecondaryLang();

            if ($redirLang) {
                $this->redirect('Translation:editor', ['lang' => $redirLang]);
            }
        }

        $this->template->title .= ' (' . $langList[$defaultLang]['name'] . ' » ' . $langList[$lang]['name'] . ')';
    }

    public function renderCreate(string $lang = ''): void
    {
    }

    public function renderEdit(string $key, string $lang = ''): void
    {
        $this->template->title .= " '$key'";
    }

    public function handleDelete(): void
    {
        if (!$this->isAjax()) {
            $this->redirect('this');
        }

        $data = $this->getHttpRequest()->getPost();

        // TODO: Permission check

        $this->translationManager->delete($data['key']);
    }

    // ##########################################
    // ###             COMPONENTS             ###
    // ##########################################

    protected function createComponentTranslationList(): \App\Components\Admin\TranslationList
    {
        $control = $this->translationList->create();
        $control->setParam([
            'search' => $this->getParameter('search'),
            'page' => $this->getParameter('page'),
        ]);

        return $control;
    }

    protected function createComponentTranslationForm(): \App\Components\Admin\TranslationForm
    {
        $form = $this->translationForm->create();
        $key = $this->getParameter('key');

        if ($key) {
            $form->setOrigin($form::OriginEdit);

            $param['key'] = $key;
            foreach ($this->translationManager->getTextListByKey($key) as $lang => $text) {
                $param["text_$lang"] = $text;
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
            $this->redirect('Translation:', ['lang' => $this->getParameter('lang')]);
        };

        $form->onError = function(string $message): void {
            $this->flashMessage($message, 'danger');
        };

        return $form;
    }

    protected function createComponentTranslationEditor(): \App\Components\Admin\TranslationEditor
    {
        $control = $this->translationEditor->create();
        $lang = $this->getParameter('lang');
        $control->setParam(['lang' => $lang]);

        $control->onSuccess = function(string $message, string $lang): void {
            $this->flashMessage($message, 'info');
            $this->redirect('Translation:editor', ['lang' => $lang]);
        };

        $control->onError = function(string $message): void {
            $this->flashMessage($message, 'danger');
        };

        return $control;
    }
}
