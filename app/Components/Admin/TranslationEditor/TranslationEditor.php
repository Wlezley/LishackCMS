<?php

declare(strict_types=1);

namespace App\Components\Admin\TranslationEditor;

use App\Components\BaseControl;
use App\Exception\TranslatorException;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;
use Nette\Utils\Json;
use Nette\Utils\JsonException;
use Webmozart\Assert\Assert;

class TranslationEditor extends BaseControl
{
    /** @var callable(string, string): void */
    public $onSuccess;

    /** @var callable(string): void */
    public $onError;

    protected function createComponentForm(): Form
    {
        $form = new Form();

        $defaultLang = $this->languageService->getDefaultLanguage($this->c('DEFAULT_LANG'));
        $languages = $this->languageService->getLanguageNames(false);
        unset($languages[$defaultLang]);

        $form->addHidden('target_lang', $this->param['lang'] ?? $this->languageService->getSecondaryLanguage());
        $form->addHidden('translations', '');
        $form->addSubmit('save', $this->t('save.translations'));

        $form->onSuccess[] = [$this, 'processSave'];
        return $form;
    }

    /**
     * @param ArrayHash<mixed> $values
     * @throws JsonException
     * @throws TranslatorException
     */
    public function processSave(Form $form, ArrayHash $values): void
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
        $this->translator->saveTranslations($translations);
        call_user_func($this->onSuccess, $this->t('success.form.translations-saved'), $values['target_lang']);
    }

    public function render(): void
    {
        $defaultLang = $this->languageService->getDefaultLanguage($this->c('DEFAULT_LANG'));
        $targetLang = $this->param['lang'] ?? $this->languageService->getSecondaryLanguage();
        Assert::nullOrStringNotEmpty($targetLang, 'Target language is not set');
        $this->template->translations = $this->translator->getTranslations($targetLang);

        $this->template->defaultLang = $defaultLang;
        $this->template->targetLang = $targetLang;
        $this->template->languages = $this->languageService->getLanguageNames(false);

        $this->getTemplate()->setFile(__DIR__ . '/TranslationEditor.latte');
        $this->getTemplate()->render();
    }
}
