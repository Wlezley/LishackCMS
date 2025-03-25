<?php

declare(strict_types=1);

namespace App\Components\Admin;

use App\Components\BaseControl;
use Nette\Application\UI\Form;

class ConfigWebsiteForm extends BaseControl
{
    public const OriginCreate = 'Create';
    public const OriginEdit = 'Edit';

    /** @var callable(string): void */
    public $onSuccess;

    /** @var callable(string): void */
    public $onError;

    public function createComponentForm(): Form
    {
        $langList = $this->translationManager->getLanguageService()->getNames();

        $form = new Form();
        $form->setHtmlAttribute('autocomplete', 'off');

        // Application
        $form->addText('APP_NAME', $this->t('app.name'))
            ->setValue($this->c('APP_NAME'))
            ->setRequired();

        $form->addText('SITE_TITLE', $this->t('site.title'))
            ->setValue($this->c('SITE_TITLE'))
            ->setRequired();

        // Language
        $form->addSelect('DEFAULT_LANG', $this->t('default_lang.website'), $langList)
            ->setValue($this->c('DEFAULT_LANG'))
            ->setRequired();

        $form->addSelect('DEFAULT_LANG_ADMIN', $this->t('default_lang.admin'), $langList)
            ->setValue($this->c('DEFAULT_LANG_ADMIN'))
            ->setRequired();

        // Google Recaptcha
        $form->addText('RECAPTCHA_SITE_KEY', $this->t('recaptcha.site_key'))
            ->setValue($this->c('RECAPTCHA_SITE_KEY'));

        $form->addText('RECAPTCHA_SECRET', $this->t('recaptcha.secret'))
            ->setValue($this->c('RECAPTCHA_SECRET'));

        // Pagination
        $form->addInteger('PAGINATION_PAGE_ITEMS', $this->t('pagination.page_items'))
            ->setValue($this->c('PAGINATION_PAGE_ITEMS'))
            ->setRequired();

        $form->addInteger('PAGINATION_MAX_PAGES', $this->t('pagination.max_pages'))
            ->setValue($this->c('PAGINATION_MAX_PAGES'))
            ->setRequired();

        // JavaScript injection
        $form->addTextArea('JS_INJECT_HEAD', $this->t('js_inject.head'), null, 4)
            ->setValue($this->c('JS_INJECT_HEAD'));

        $form->addTextArea('JS_INJECT_BODY_FIRST', $this->t('js_inject.body_first'), null, 4)
            ->setValue($this->c('JS_INJECT_BODY_FIRST'));

        $form->addTextArea('JS_INJECT_BODY_LAST', $this->t('js_inject.body_last'), null, 4)
            ->setValue($this->c('JS_INJECT_BODY_LAST'));

        $form->addTextArea('JS_IP_EXCEPTIONS', $this->t('js_inject.ip_exceptions'), null, 4)
            ->setValue($this->c('JS_IP_EXCEPTIONS'));

        // CSS injection
        $form->addTextArea('CSS_INJECT', $this->t('css_inject'), null, 4)
            ->setValue($this->c('CSS_INJECT'));

        $form->addSubmit('save', $this->t('save.config'));

        $form->onSuccess[] = [$this, 'processSave'];

        return $form;
    }

    /** @param \Nette\Utils\ArrayHash<mixed> $values */
    public function processSave(Form $form, \Nette\Utils\ArrayHash $values): void
    {
        $required = [
            ['name' => 'APP_NAME', 'label.key' => 'app.name'],
            ['name' => 'SITE_TITLE', 'label.key' => 'site.title'],
            ['name' => 'DEFAULT_LANG', 'label.key' => 'default_lang.website'],
            ['name' => 'DEFAULT_LANG_ADMIN', 'label.key' => 'default_lang.admin'],
            ['name' => 'PAGINATION_PAGE_ITEMS', 'label.key' => 'pagination.page_items'],
            ['name' => 'PAGINATION_MAX_PAGES', 'label.key' => 'pagination.max_pages'],
        ];

        foreach ($required as $item) {
            if (empty($values[$item['name']])) {
                $label = $this->t($item['label.key']);
                call_user_func($this->onError, $this->tf('error.form.missing-required.settings', $label));
                return;
            }
        }

        $this->configManager->saveConfigValues((array)$values);
        call_user_func($this->onSuccess, $this->t('success.form.settings-saved'));
    }

    public function render(): void
    {
        $this->template->configuration = $this->configManager->getConfigData();

        $this->template->setFile(__DIR__ . '/ConfigWebsiteForm.latte');
        $this->template->render();
    }
}
