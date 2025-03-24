<?php

declare(strict_types=1);

namespace App\Components\Admin;

use App\Components\BaseControl;
use Nette\Application\UI\Form;

class ConfigSeoForm extends BaseControl
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

        // OPEN GRAPH PROTOCOL
        $form->addText('OG_TITLE', $this->t('open-graph.title'))
            ->setValue($this->c('OG_TITLE'));

        $form->addText('OG_DESCRIPTION', $this->t('open-graph.description'))
            ->setValue($this->c('OG_DESCRIPTION'));

        $form->addText('OG_IMAGE', $this->t('open-graph.image'))
            ->setValue($this->c('OG_IMAGE'));

        $form->addCheckbox('OG_SHOW_LOCALE', $this->t('open-graph.show-locale'))
            ->setValue($this->c('OG_SHOW_LOCALE'));

        // SEO
        $form->addText('SEO_TITLE', $this->t('seo.title'))
            ->setValue($this->c('SEO_TITLE'));

        $form->addText('SEO_DESCRIPTION', $this->t('seo.description'))
            ->setValue($this->c('SEO_DESCRIPTION'));

        $form->addText('SEO_INDEX', $this->t('seo.index'))
            ->setValue($this->c('SEO_INDEX'))
            ->setRequired();

        $form->addTextArea('SEO_ROBOTS', $this->t('robots.txt'), null, 4)
            ->setValue($this->c('SEO_ROBOTS'))
            ->setRequired();

        $form->addSubmit('save', $this->t('save.config'));

        $form->onSuccess[] = [$this, 'processSave'];

        return $form;
    }

    /** @param \Nette\Utils\ArrayHash<mixed> $values */
    public function processSave(Form $form, \Nette\Utils\ArrayHash $values): void
    {
        $required = [
            ['name' => 'SEO_INDEX', 'label.key' => 'seo.index'],
            ['name' => 'SEO_ROBOTS', 'label.key' => 'robots.txt'],
        ];

        foreach ($required as $item) {
            if (empty($values[$item['name']])) {
                $label = $this->t($item['label.key']);
                call_user_func($this->onError, "Povinná položka nastavení '$label' je prázdná. Nastavení nebylo uloženo.");
                return;
            }
        }

        $this->configManager->saveConfigValues((array)$values);
        call_user_func($this->onSuccess, 'Nastavení bylo uloženo.');
    }

    public function render(): void
    {
        $this->template->configuration = $this->configManager->getConfigData();

        $this->template->setFile(__DIR__ . '/ConfigSeoForm.latte');
        $this->template->render();
    }
}
