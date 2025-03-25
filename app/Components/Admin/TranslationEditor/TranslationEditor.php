<?php

declare(strict_types=1);

namespace App\Components\Admin;

use App\Components\BaseControl;
use Nette\Application\UI\Form;
use Nette\Utils\Json;

class TranslationEditor extends BaseControl
{
    /** @var callable(string, string): void */
    public $onSuccess;

    /** @var callable(string): void */
    public $onError;

    protected function createComponentForm(): Form
    {
        $form = new Form;

        $languageService = $this->translationManager->getLanguageService();
        $defaultLang = $languageService->getDefaultLang($this->c('DEFAULT_LANG'));
        $languages = $languageService->getNames(false);
        unset($languages[$defaultLang]);

        $form->addHidden('target_lang', $this->param['lang'] ?? $languageService->getSecondaryLang('en'));
        $form->addHidden('translations', '');
        $form->addSubmit('save', $this->t('save.translations'));

        $form->onSuccess[] = [$this, 'processSave'];
        return $form;
    }

    /** @param \Nette\Utils\ArrayHash<mixed> $values */
    public function processSave(Form $form, \Nette\Utils\ArrayHash $values): void
    {
        if (empty($values['translations'])) {
            call_user_func($this->onError, $this->t('error.form.empty-data'));
            return;
        }

        if (empty($values['target_lang'])) {
            call_user_func($this->onError, $this->t('error.form.empty-target-lang'));
            return;
        }

        $translations = Json::decode($values['translations'], true);
        $this->translationManager->saveTranslations($translations);
        call_user_func($this->onSuccess, $this->t('success.form.translations-saved'), $values['target_lang']);
    }

    public function render(): void
    {
        $languageService = $this->translationManager->getLanguageService();
        $defaultLang = $languageService->getDefaultLang($this->c('DEFAULT_LANG'));
        $targetLang = $this->param['lang'] ?? $languageService->getSecondaryLang('en');
        $this->template->translations = $this->translationManager->getTranslations($targetLang);

        $this->template->defaultLang = $defaultLang;
        $this->template->targetLang = $targetLang;
        $this->template->languages = $languageService->getNames(false);

        $this->template->setFile(__DIR__ . '/TranslationEditor.latte');
        $this->template->render();
    }
}
