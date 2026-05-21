<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presenters;

use App\Components\Admin\TranslationEditor\ITranslationEditorFactory;
use App\Components\Admin\TranslationForm\ITranslationFormFactory;
use App\Components\Admin\TranslationList\ITranslationListFactory;
use App\Exception\TranslatorException;
use Nette\Application\UI\Template;
use Nette\Bridges\ApplicationLatte\DefaultTemplate;

/**
 * @property-read Template|DefaultTemplate|\stdClass $template
 */
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
        $lang = $lang ?? $this->languageService->getDefaultLanguage($this->c('DEFAULT_LANG'));

        try {
            $languageDto = $this->languageService->getLanguage($lang);
        } catch (TranslatorException) {
            $this->redirect('Translation:');
        }

        $this->template->title .= ' - ' . $languageDto->name . ($languageDto->default ? ' (' . $this->t('default') . ')' : '');

        $this->template->lang = $lang;
        $this->template->langList = $this->languageService->getAvailableLanguages(false);
        $this->template->search = $search;
    }

    public function renderEditor(string $lang = ''): void
    {
        $availableLanguages = $this->languageService->getAvailableLanguages(false);
        $defaultLanguage = $this->languageService->getDefaultLanguage($this->c('DEFAULT_LANG'));

        if (empty($lang) || $lang == $defaultLanguage || !array_key_exists($lang, $availableLanguages)) {
            $redirectLanguage = $this->languageService->getSecondaryLanguage();

            if ($redirectLanguage) {
                $this->redirect('Translation:editor', ['lang' => $redirectLanguage]);
            }
        }

        $this->template->title .= ' (' . $availableLanguages[$defaultLanguage]->name . ' » ' . $availableLanguages[$lang]->name . ')';
    }

    public function renderCreate(string $lang = ''): void
    {
    }

    public function renderEdit(string $key, string $lang = ''): void
    {
        if (!$this->translator->existsInDB($key, null)) {
            $this->flashMessage($this->tf('translation.key.not-found', $key), 'danger');
            $this->redirect(':default');
        }

        $this->template->title .= " '$key'";
    }

    public function handleDelete(): void
    {
        if (!$this->isAjax()) {
            $this->redirect('this');
        }

        $data = $this->getHttpRequest()->getPost();

        // TODO: Permission check

        $this->translator->delete($data['key']);
    }

    // ##########################################
    // ###             COMPONENTS             ###
    // ##########################################

    protected function createComponentTranslationList(): \App\Components\Admin\TranslationList\TranslationList
    {
        $control = $this->translationList->create();
        $control->setParam([
            'search' => $this->getParameter('search'),
            'page' => $this->getParameter('page'),
        ]);

        return $control;
    }

    protected function createComponentTranslationForm(): \App\Components\Admin\TranslationForm\TranslationForm
    {
        $form = $this->translationForm->create();
        $key = $this->getParameter('key');

        if ($key) {
            $form->setOrigin($form::OriginEdit);

            $param['key'] = $key;
            foreach ($this->translator->getTextListByKey($key) as $lang => $text) {
                $param["text_$lang"] = $text;
            }

            $form->setParam($param);
        } else {
            $form->setOrigin($form::OriginCreate);
            $form->setQueryParams($this->getHttpRequest()->getQuery());
            $form->setParam($this->getHttpRequest()->getPost('param'));
        }

        $form->setLanguageList(
            $this->languageService->getAvailableLanguages(false)
        );

        $form->onSuccess = function (string $message): void {
            $this->flashMessage($message, 'info');
            $this->redirect('Translation:', ['lang' => $this->getParameter('lang')]);
        };

        $form->onError = function (string $message): void {
            $this->flashMessage($message, 'danger');
        };

        return $form;
    }

    protected function createComponentTranslationEditor(): \App\Components\Admin\TranslationEditor\TranslationEditor
    {
        $control = $this->translationEditor->create();
        $lang = $this->getParameter('lang');
        $control->setParam(['lang' => $lang]);

        $control->setTranslator($this->translator);
        $control->setLanguageService($this->languageService);

        $control->onSuccess = function (string $message, string $lang): void {
            $this->flashMessage($message, 'info');
            $this->redirect('Translation:editor', ['lang' => $lang]);
        };

        $control->onError = function (string $message): void {
            $this->flashMessage($message, 'danger');
        };

        return $control;
    }
}
