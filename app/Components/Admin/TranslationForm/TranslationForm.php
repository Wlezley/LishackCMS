<?php

declare(strict_types=1);

namespace App\Components\Admin;

use App\Components\BaseControl;
use Nette\Application\UI\Form;

class TranslationForm extends BaseControl
{
    public const OriginCreate = 'Create';
    public const OriginEdit = 'Edit';

    private string $origin;

    /** @var array<string,array<string,mixed>> */
    private array $languageList;

    /** @var array<string,string> $queryParams */
    private array $queryParams;

    /** @var callable(string): void */
    public $onSuccess;

    /** @var callable(string): void */
    public $onError;

    public function createComponentForm(): Form
    {
        $param = $this->param;

        $form = new Form();

        $form->setHtmlAttribute('autocomplete', 'off');

        $form->addText('key', $this->t('key'))
            ->setHtmlAttribute('autocomplete', 'off')
            ->setValue($param['key'] ?? '')
            ->setRequired();

        foreach ($this->languageList as $key => $langData) {
            $required = $langData['default'] == 1;
            $form->addTextArea("text_$key", $this->t('text') . " ($langData[name])", null, 1)
                ->setHtmlAttribute('autocomplete', 'off')
                ->setValue($param["text_$key"] ?? '')
                ->setRequired($required);
        }

        $form->addSubmit('save');

        $form->onSuccess[] = [$this, 'process' . $this->origin];

        return $form;
    }

    /** @param \Nette\Utils\ArrayHash<mixed> $values */
    public function processCreate(Form $form, \Nette\Utils\ArrayHash $values): void
    {
        if (!empty($this->translationManager->getTextListByKey($values['key']))) {
            $editLink = $this->buildEditUrl($values['key']);
            $editAnchor = '<a href="' . $editLink . '" target="_blank">' . $values['key'] . ' <sup><i class="fa fa-external-link"></i></sup></a>';
            call_user_func($this->onError, $this->tf('error.form.translation-duplicate-key', $values['key'], $editAnchor));
        } else {
            foreach ($this->languageList as $lang => $langData) {
                if (isset($values["text_$lang"])) {
                    if (empty($values["text_$lang"])) {
                        continue;
                    }

                    $this->translationManager->add($values['key'], $lang, $values["text_$lang"]);
                }
            }

            if (!empty($this->translationManager->getTextListByKey($values['key']))) {
                call_user_func($this->onSuccess, $this->tf('success.form.translation-created.named', $values['key']));
            } else {
                call_user_func($this->onError, $this->t('error.form.translation-create'));
            }
        }
    }

    /** @param \Nette\Utils\ArrayHash<mixed> $values */
    public function processEdit(Form $form, \Nette\Utils\ArrayHash $values): void
    {
        $key = $values['key'];
        $textList = $this->translationManager->getTextListByKey($key);

        foreach ($this->languageList as $lang => $langName) {
            if (isset($textList[$lang])) {
                if (empty($values["text_$lang"])) {
                    $this->translationManager->delete($key, $lang);
                } else {
                    $this->translationManager->update($key, $lang, $values["text_$lang"]);
                }
            } elseif (!empty($values["text_$lang"])) {
                $this->translationManager->add($key, $lang, $values["text_$lang"]);
            }
        }

        call_user_func($this->onSuccess, $this->t('success.form.translation-saved'));
    }

    public function render(int|string|null $key = null): void
    {
        $this->template->languageList = $this->languageList;
        $this->template->setFile(__DIR__ . '/TranslationForm.latte');
        $this->template->render();
    }

    public function setOrigin(string $origin): void
    {
        $this->origin = $origin;
    }

    /** @param array<string,array<string,mixed>> $languageList */
    public function setLanguageList(array $languageList): void
    {
        $this->languageList = $languageList;
    }

    /** @param array<string,string> $queryParams */
    public function setQueryParams(array $queryParams): void
    {
        unset($queryParams['page']);
        $this->queryParams = $queryParams;
    }

    private function buildEditUrl(string $key): string
    {
        $params = http_build_query(array_merge($this->queryParams, ['key' => $key]));
        return '?' . $params;
    }
}
