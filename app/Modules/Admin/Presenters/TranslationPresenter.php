<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presenters;

use App\Components\Admin\ITranslationFormFactory;
use App\Components\Admin\ITranslationEditorFactory;
use Nette\Utils\Json;

class TranslationPresenter extends SecuredPresenter
{
    /** @var ITranslationFormFactory @inject */
    public ITranslationFormFactory $translationForm;

    /** @var ITranslationEditorFactory @inject */
    public ITranslationEditorFactory $translationEditor;

    public function renderDefault(int $page = 1, ?string $lang = null, ?string $search = null): void
    {
        $languageService = $this->translationManager->getLanguageService();

        $langList = $languageService->getList(false);
        $defaultLang = $languageService->getDefaultLang(DEFAULT_LANG);
        $lang = $lang ?? $defaultLang;

        $langData = $languageService->getLanguage($lang);
        if ($langData === null) {
            $this->redirect('Translation:');
        }

        $this->template->title = 'Lokalizace - ' . $langData['name'] . ($langData['default'] ? ' (' . $this->t('default') . ')' : '');

        $limit = 10;
        $offset = ($page - 1) * $limit;

        $this->template->translations = $this->translationManager->getList($lang, $limit, $offset, $search);

        $totalItems = $this->translationManager->getCount($lang, $search);
        $this->setPagination($limit, $totalItems);

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
        $this->template->defaultLang = $defaultLang;
        $this->template->langList = $langList;
        $this->template->totalItems = $totalItems;
        $this->template->search = $search;
    }

    public function renderEditor(string $lang = ''): void
    {
        $languageService = $this->translationManager->getLanguageService();

        $langList = $languageService->getList(false);
        $defaultLang = $languageService->getDefaultLang(DEFAULT_LANG);

        if (empty($lang) || $lang == $defaultLang || !array_key_exists($lang, $langList)) {
            $redirLang = $languageService->getSecondaryLang();

            if ($redirLang) {
                $this->redirect('Translation:editor', ['lang' => $redirLang]);
            }
        }

        $this->template->title = 'Editor Lokalizace (' . $langList[$defaultLang]['name'] . ' » ' . $langList[$lang]['name'] . ')';
    }

    public function renderCreate(): void
    {
        $this->template->title = 'Nový překlad';
    }

    public function renderEdit(string $key): void
    {
        $this->template->title = "Editace překladu '$key'";
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
            $this->redirect('Translation:');
        };

        $form->onError = function(string $message): void {
            $this->flashMessage($message, 'danger');
        };

        return $form;
    }

    protected function createComponentTranslationEditor(): \App\Components\Admin\TranslationEditor
    {
        $form = $this->translationEditor->create();
        $lang = $this->getParameter('lang');
        $form->setParam(['lang' => $lang]);

        $form->onSuccess = function(string $message, string $lang): void {
            $this->flashMessage($message, 'info');
            $this->redirect('Translation:editor', ['lang' => $lang]);
        };

        $form->onError = function(string $message): void {
            $this->flashMessage($message, 'danger');
        };

        return $form;
    }
}
