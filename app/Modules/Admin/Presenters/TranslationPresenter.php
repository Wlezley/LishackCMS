<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presenters;

use App\Components\Admin\TranslationEditor\ITranslationEditorFactory;
use App\Components\Admin\TranslationEditor\TranslationEditor;
use App\Components\Admin\TranslationForm\ITranslationFormFactory;
use App\Components\Admin\TranslationForm\TranslationForm;
use App\Components\Admin\TranslationList\ITranslationListFactory;
use App\Components\Admin\TranslationList\TranslationList;
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
        try {
            $language = $lang === null
                ? $this->languageService->getDefaultLanguage()
                : $this->languageService->getLanguage($lang);
        } catch (TranslatorException) {
            $this->redirect('Translation:');
        }

        $this->template->title .= ' - ' . $language->getName() . ($language->isDefault() ? ' (' . $this->t('default') . ')' : '');

        $this->template->lang = $language->getCode();
        $this->template->language = $language;
        $this->template->langList = $this->languageService->getAvailableLanguages(false);
        $this->template->search = $search;
    }

    public function renderEditor(string $lang = ''): void
    {
        $availableLanguages = $this->languageService->getAvailableLanguages(false);
        $defaultLanguage = $this->languageService->getDefaultLanguage();

        if (empty($lang) || $lang === $defaultLanguage->getCode() || !array_key_exists($lang, $availableLanguages)) {
            $redirectLanguage = $this->languageService->getSecondaryLanguage();

            if ($redirectLanguage) {
                $this->redirect('Translation:editor', ['lang' => $redirectLanguage]);
            }
        }

        $sourceLanguageName = $availableLanguages[$defaultLanguage->getCode()]->getName();
        $targetLanguageName = $availableLanguages[$lang]->getName();

        $this->template->title .= ' (' . $sourceLanguageName . ' » ' . $targetLanguageName . ')';
    }

    public function renderCreate(string $lang = ''): void
    {
    }

    public function renderEdit(string $key, string $lang = ''): void
    {
        if (!$this->translationService->keyExists($key)) {
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

        $this->translationService->delete($data['key']);
    }

    // ##########################################
    // ###             COMPONENTS             ###
    // ##########################################

    protected function createComponentTranslationList(): TranslationList
    {
        $control = $this->translationList->create();
        $control->setParam([
            'search' => $this->getParameter('search'),
            'page' => $this->getParameter('page'),
        ]);

        return $control;
    }

    protected function createComponentTranslationForm(): TranslationForm
    {
        $form = $this->translationForm->create();
        $key = $this->getParameter('key');

        if ($key) {
            $form->setOrigin($form::OriginEdit);

            $param['key'] = $key;
            foreach ($this->translationService->getTextListByKey($key) as $lang => $text) {
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

    protected function createComponentTranslationEditor(): TranslationEditor
    {
        $control = $this->translationEditor->create();
        $lang = $this->getParameter('lang');
        $control->setParam(['lang' => $lang]);

        $control->setTranslationService($this->translationService);
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
