<?php

declare(strict_types=1);

namespace App\Components\Admin;

use App\Components\BaseControl;
use Nette\Application\UI\Form;
use Nette\Utils\Json;

class ConfigEditor extends BaseControl
{
    /** @var callable(string): void */
    public $onSuccess;

    /** @var callable(string): void */
    public $onError;

    protected function createComponentForm(): Form
    {
        $form = new Form;

        $form->addHidden('configuration', '');
        $form->addSubmit('save', $this->t('save.config'));

        $form->onSuccess[] = [$this, 'processSave'];
        return $form;
    }

    /** @param \Nette\Utils\ArrayHash<mixed> $values */
    public function processSave(Form $form, \Nette\Utils\ArrayHash $values): void
    {
        if (empty($values['configuration'])) {
            call_user_func($this->onError, $this->t('error.form.empty-data'));
            return;
        }

        $configuration = Json::decode($values['configuration'], true);
        $this->configManager->saveConfig($configuration);
        call_user_func($this->onSuccess, 'Nastavení bylo uloženo.');
    }

    public function render(): void
    {
        $this->template->configuration = $this->configManager->getConfigData();

        $this->template->setFile(__DIR__ . '/ConfigEditor.latte');
        $this->template->render();
    }
}
