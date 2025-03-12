<?php

declare(strict_types=1);

namespace App\Components\Admin;

use App\Components\BaseControl;
use App\Models\UserManager;
use App\Models\UserValidator;
use Nette\Application\UI\Form;

class UserForm extends BaseControl
{
    public const OriginCreate = 'Create';
    public const OriginEdit = 'Edit';

    protected string $origin;

    /** @var callable(\Nette\Utils\ArrayHash<mixed>|array<mixed>): void */
    public $onSuccess;

    /** @var callable(string): void */
    public $onError;

    public function __construct(
        protected \Nette\Security\User $user,
        protected UserManager $userManager
    ) {
        $this->param = [];
    }

    public function createComponentForm(): Form
    {
        $param = $this->param;

        if (empty($param)) {
            $param = [
                'name' => '',
                'full_name' => '',
                'email' => '',
                'deleted' => false,
                'enabled' => true,
            ];
        } else {
            unset($param['password']);
            unset($param['password2']);
        }

        bdump($param, "FORM PARAM");

        $this->validateOrigin($this->origin);


        $form = new Form();

        $form->setHtmlAttribute('autocomplete', 'off');

        if (isset($param['id'])) {
            $form->addHidden('id', $param['id']);
        }

        $form->addHidden('origin', $this->origin);

        $form->addText('name', 'Přihlašovací jméno')
            ->setHtmlAttribute('placeholder', 'Přihlašovací jméno')
            ->setValue($param['name'])
            ->setRequired();

        $form->addText('full_name', 'Celé jméno')
            ->setHtmlAttribute('placeholder', 'Celé jméno')
            ->setValue($param['full_name'])
            ->setRequired();

        $form->addEmail('email', 'E-mail')
            ->setHtmlAttribute('placeholder', 'E-mail')
            ->setValue($param['email']);

        $form->addCheckbox('deleted', 'Smazáno')
            ->setValue($param['deleted']);

        $form->addCheckbox('enabled', 'Aktivní uživatel')
            ->setValue($param['enabled']);

        if ($this->origin === self::OriginEdit) {
            $form->addCheckbox('change_password', 'Změnit heslo')
                ->setValue(false);
        }

        $form->addPassword('password', 'Heslo')
            ->setHtmlAttribute('placeholder', 'Heslo')
            ->setHtmlAttribute('autocomplete', 'new-password')
            ->setRequired($this->origin === self::OriginCreate);

        $form->addPassword('password2', 'Heslo znovu')
            ->setHtmlAttribute('placeholder', 'Heslo znovu')
            ->setHtmlAttribute('autocomplete', 'new-password')
            ->setRequired($this->origin === self::OriginCreate);

        $form->addSubmit('save', $this->origin === self::OriginEdit ? 'Uložit' : 'Vytvořit');

        $form->onSuccess[] = [$this, 'process' . $this->origin];

        return $form;
    }

    /** @param \Nette\Utils\ArrayHash<mixed> $values */
    public function processCreate(Form $form, \Nette\Utils\ArrayHash $values): void
    {
        bdump($values, 'FORM SUBMITTED VALUES (CR)');

        try {
            $this->validateOrigin($values['origin']);
        } catch (\Exception $e) {
            call_user_func($this->onError, $e->getMessage());
        }

        if (empty($values['password'])) {
            call_user_func($this->onError, 'Vyplňte heslo.');
            return;
        } elseif ($values['password'] !== $values['password2']) {
            call_user_func($this->onError, 'Hesla se neshodují.');
            return;
        }

        $formatedValues = UserValidator::prepareData((array)$values, false);
        bdump($formatedValues, 'FORM FORMATED VALUES (CR)');
        call_user_func($this->onSuccess, $formatedValues);
    }

    /** @param \Nette\Utils\ArrayHash<mixed> $values */
    public function processEdit(Form $form, \Nette\Utils\ArrayHash $values): void
    {
        bdump($values, 'FORM SUBMITTED VALUES (ED)');

        try {
            $this->validateOrigin($values['origin']);
        } catch (\Exception $e) {
            call_user_func($this->onError, $e->getMessage());
        }

        if ($values['change_password'] && $values['password'] !== $values['password2']) {
            call_user_func($this->onError, 'Hesla se neshodují.');
            return;
        }

        $formatedValues = UserValidator::prepareData((array)$values, false);
        // $formatedValues['id'] = $values['id'];
        bdump($formatedValues, 'FORM FORMATED VALUES (ED)');
        call_user_func($this->onSuccess, $formatedValues);
    }

    public function render(int|string|null $id = null): void
    {
        try {
            $this->validateOrigin($this->origin);

            if ($this->origin === self::OriginEdit) {
                if (empty($id)) {
                    throw new \Exception('User ID is missing.');
                }

                $this->param = $this->userManager->get((int) $id);
            }
        } catch(\Exception $e) {
            call_user_func($this->onError, $e->getMessage());
        }

        $this->template->setFile(__DIR__ . '/UserForm' . $this->origin . '.latte');
        $this->template->render();
    }

    public function setUserManager(UserManager $userManager): void
    {
        $this->userManager = $userManager;
    }

    public function setOrigin(string $origin): void
    {
        $this->validateOrigin($origin);
        $this->origin = $origin;
    }

    private function validateOrigin(string $origin): void
    {
        if (empty($origin)) {
            throw new \Exception('Form origin is missing.');
        } elseif (!in_array($origin, [
            self::OriginCreate,
            self::OriginEdit
        ], true)) {
            throw new \Exception('Form origin is incorrect.');
        }
    }
}

