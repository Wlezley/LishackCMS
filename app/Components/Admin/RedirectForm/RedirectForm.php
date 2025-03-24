<?php

declare(strict_types=1);

namespace App\Components\Admin;

use App\Components\BaseControl;
use App\Models\RedirectException;
use App\Models\RedirectManager;
use Nette\Application\UI\Form;

class RedirectForm extends BaseControl
{
    public const OriginCreate = 'Create';
    public const OriginEdit = 'Edit';

    private string $origin;

    /** @var callable(string, int): void */
    public $onSuccess;

    /** @var callable(string): void */
    public $onError;

    public function __construct(
        private RedirectManager $redirectManager
    ) {}

    public function createComponentForm(): Form
    {
        $param = $this->param;

        $form = new Form();

        $form->setHtmlAttribute('autocomplete', 'off');

        if ($this->origin == self::OriginEdit) {
            $form->addHidden('id', $param['id']);
            $form->addHidden('page', $param['page'] ?? 1);
        }

        $form->addText('source', $this->t('source-url'))
            ->setHtmlAttribute('autocomplete', 'off')
            ->setValue($param['source'] ?? '')
            ->setRequired();

        $form->addText('target', $this->t('target-url'))
            ->setHtmlAttribute('autocomplete', 'off')
            ->setValue($param['target'] ?? '')
            ->setRequired();

        $form->addSelect('code', $this->t('http-code'), RedirectManager::REDIRECT_HTTP_CODES)
            ->setValue($param['code'] ?? 302)
            ->setRequired();

        $form->addCheckbox('enabled', $this->t('active'))
            ->setValue($param['enabled'] ?? 1);

        $form->addSubmit('save');

        $form->onSuccess[] = [$this, 'process'];

        return $form;
    }

    /** @param \Nette\Utils\ArrayHash<mixed> $values */
    public function process(Form $form, \Nette\Utils\ArrayHash $values): void
    {
        $required = [
            ['name' => 'source', 'label.key' => 'source-url'],
            ['name' => 'target', 'label.key' => 'target-url'],
            ['name' => 'code', 'label.key' => 'http-code'],
        ];

        if ($this->origin == self::OriginEdit) {
            $required[] = ['name' => 'id', 'label.key' => 'id'];
        }

        foreach ($required as $item) {
            if (empty($values[$item['name']])) {
                $label = $this->t($item['label.key']);
                call_user_func($this->onError, "Povinná položka nastavení '$label' je prázdná. Nastavení nebylo uloženo.");
                return;
            }
        }

        if (!$this->redirectManager->checkHttpCode($values['code'])) {
            call_user_func($this->onError, "Neplatný HTTP code '$values[code]'. Nastavení nebylo uloženo.");
            return;
        }

        try {
            if ($this->origin == self::OriginCreate) {
                $this->redirectManager->add($values['source'], $values['target'], $values['code'], $values['enabled'] == 1);
                call_user_func($this->onSuccess, "Přesměrování bylo vytvořeno", 1);
            } else if ($this->origin == self::OriginEdit) {
                $this->redirectManager->update($values['id'], $values['source'], $values['target'], $values['code'], $values['enabled'] == 1);
                call_user_func($this->onSuccess, 'Přesměrování bylo upraveno', $values['page'] ?? 1);
            } else {
                call_user_func($this->onError, 'Chyba: Neznámý typ formuláře');
                return;
            }
        } catch (RedirectException $e) {
            call_user_func($this->onError, 'Chyba: ' . $e->getMessage());
        }
    }

    public function render(): void
    {
        $this->template->setFile(__DIR__ . '/RedirectForm.latte');
        $this->template->render();
    }

    public function setOrigin(string $origin): void
    {
        $this->origin = $origin;
    }
}
