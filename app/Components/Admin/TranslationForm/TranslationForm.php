<?php

declare(strict_types=1);

namespace App\Components\Admin\TranslationForm;

use App\Components\BaseControl;
use App\Entity\Language\Language;
use App\Exception\TranslatorException;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;

class TranslationForm extends BaseControl
{
    public const string OriginCreate = 'Create';
    public const string OriginEdit = 'Edit';

    private string $origin;

    /** @var array<string, Language> */
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

        foreach ($this->languageList as $language) {
            $form->addTextArea("text_{$language->getCode()}", $this->t('text') . " ({$language->getName()})", null, 1)
                ->setHtmlAttribute('autocomplete', 'off')
                ->setValue($param["text_{$language->getCode()}"] ?? '')
                ->setRequired($language->isDefault());
        }

        $form->addSubmit('save');

        $form->onSuccess[] = [$this, 'process' . $this->origin]; // @phpstan-ignore-line

        return $form;
    }

    /**
     * @param ArrayHash<mixed> $values
     * @throws TranslatorException
     */
    public function processCreate(Form $form, ArrayHash $values): void
    {
        if (!empty($this->translationService->getTextListByKey($values['key']))) {
            $editLink = $this->buildEditUrl($values['key']);
            $editAnchor =
                '<a href="' . $editLink . '" target="_blank">'
                    . $values['key'] .
                ' <sup><i class="fa fa-external-link"></i></sup></a>';
            call_user_func($this->onError, $this->tf('error.form.translation-duplicate-key', $values['key'], $editAnchor));
        } else {
            foreach ($this->languageList as $languageCode => $languageDto) {
                if (isset($values["text_$languageCode"])) {
                    if (empty($values["text_$languageCode"])) {
                        continue;
                    }

                    $this->translationService->add($values['key'], $languageCode, $values["text_$languageCode"]);
                }
            }

            if (!empty($this->translationService->getTextListByKey($values['key']))) {
                call_user_func($this->onSuccess, $this->tf('success.form.translation-created.named', $values['key']));
            } else {
                call_user_func($this->onError, $this->t('error.form.translation-create'));
            }
        }
    }

    /**
     * @param ArrayHash<mixed> $values
     * @throws TranslatorException
     */
    public function processEdit(Form $form, ArrayHash $values): void
    {
        $key = $values['key'];
        $textList = $this->translationService->getTextListByKey($key);

        foreach ($this->languageList as $languageCode => $languageDto) {
            if (isset($textList[$languageCode])) {
                if (empty($values["text_$languageCode"])) {
                    $this->translationService->delete($key, $languageCode);
                } else {
                    $this->translationService->update($key, $languageCode, $values["text_$languageCode"]);
                }
            } elseif (!empty($values["text_$languageCode"])) {
                $this->translationService->add($key, $languageCode, $values["text_$languageCode"]);
            }
        }

        call_user_func($this->onSuccess, $this->t('success.form.translation-saved'));
    }

    public function render(int|string|null $key = null): void
    {
        $this->template->languageList = $this->languageList;
        $this->getTemplate()->setFile(__DIR__ . '/TranslationForm.latte');
        $this->getTemplate()->render();
    }

    public function setOrigin(string $origin): void
    {
        $this->origin = $origin;
    }

    /**
     * @param array<string, Language> $languageList
     */
    public function setLanguageList(array $languageList): void
    {
        $this->languageList = $languageList;
    }

    /**
     * @param array<string,string> $queryParams
     */
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
