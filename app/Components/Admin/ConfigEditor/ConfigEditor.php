<?php

declare(strict_types=1);

namespace App\Components\Admin\ConfigEditor;

use App\Components\BaseControl;
use App\Exception\ConfigException;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;
use Nette\Utils\Json;
use Nette\Utils\JsonException;

class ConfigEditor extends BaseControl
{
    /** @var callable(string): void */
    public $onSuccess;

    /** @var callable(string): void */
    public $onError;

    protected function createComponentForm(): Form
    {
        $form = new Form();

        $form->addHidden('configuration', '');
        $form->addSubmit('save', $this->t('save.config'));

        $form->onSuccess[] = [$this, 'processSave'];
        return $form;
    }

    /**
     * @param ArrayHash<mixed> $values
     * @throws ConfigException
     * @throws JsonException
     */
    public function processSave(Form $form, ArrayHash $values): void
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

        $this->getTemplate()->setFile(__DIR__ . '/ConfigEditor.latte');
        $this->getTemplate()->render();
    }
}
