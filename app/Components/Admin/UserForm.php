<?php

declare(strict_types=1);

namespace App\Components\Admin;

use App\Components\BaseControl;
use App\Models\UserManager;
use Nette\Application\UI\Form;

class UserForm extends BaseControl
{
    /** @var null|array<string,string> $param */
    protected ?array $param = [];

    /** @var array<callable(\Nette\Utils\ArrayHash<mixed>): void> */
    public array $onSuccess = [];

    /** @var array<callable(string): void> */
    public array $onError = [];

    public function __construct(
        protected \Nette\Security\User $user,
        protected UserManager $userManager
    ) {
        $this->param = [];
    }

    public function createComponentForm(): Form
    {
        $param = $this->param;
        $isEdit = false;

        if (empty($param)) {
            $param = [
                'name' => '',
                'full_name' => '',
                'email' => '',
                'deleted' => false,
                'enabled' => true,
            ];
        } else {
            $isEdit = true;
            unset($param['password']);
        }

        $form = new Form();

        $form->setHtmlAttribute('autocomplete', 'off');

        if (isset($param['id'])) {
            $form->addHidden('id', $param['id']);
        }

        $form->addText('name', 'Přihlašovací jméno')
            ->setHtmlAttribute('placeholder', 'Přihlašovací jméno')
            ->setValue($param['name'])
            ->setRequired();

        $form->addText('full_name', 'Celé jméno')
            ->setHtmlAttribute('placeholder', 'Celé jméno')
            ->setValue($param['full_name']);

        $form->addEmail('email', 'E-mail')
            ->setHtmlAttribute('placeholder', 'E-mail')
            ->setValue($param['email']);

        $form->addCheckbox('deleted', 'Smazáno')
            ->setValue($param['deleted']);

        $form->addCheckbox('enabled', 'Aktivní uživatel')
            ->setValue($param['enabled']);

        $form->addPassword('password', 'Heslo')
            ->setHtmlAttribute('placeholder', 'Heslo')
            ->setHtmlAttribute('autocomplete', 'new-password')
            ->setRequired(!$isEdit);

        $form->addPassword('password2', 'Heslo znovu')
            ->setHtmlAttribute('placeholder', 'Heslo znovu')
            ->setHtmlAttribute('autocomplete', 'new-password')
            ->setRequired(!$isEdit);

        $form->addSubmit('save', $isEdit ? 'Uložit' : 'Vytvořit');

        $form->onSuccess[] = [$this, 'process'];

        return $form;
    }

    /** @param \Nette\Utils\ArrayHash<mixed> $values */
    public function process(Form $form, \Nette\Utils\ArrayHash $values): void
    {
        if ($values['password'] === $values['password2']) {
            $this->onSuccess[] = [$values];
            // $this->onSuccess[] = [$this, $values];
        } else {
            $this->onError[]= ['Hesla se neshodují.'];
            // $this->onError[]= [$this, 'Hesla se neshodují.'];
        }

        // try {
        //     if ($values['password'] === $values['password2']) {
        //         $userID = $this->userManager->create((array)$values);
        //         if ($userID > 1) {
        //             $this->onSuccess($this, $userID);

        //             // $this->flashMessage("Uživatel byl vytvořen (ID: $userID).", 'info');
        //             // $this->redirect('User:default');
        //         }
        //     } else {
        //         $this->onError($this, 'Hesla se neshodují.');

        //         // $this->flashMessage('Hesla se neshodují...', 'danger');
        //     }
        // } catch(\Exception $e) {
        //     bdump($e);
        //     // $this->onError($this, $e->getMessage());
        //     // $this->flashMessage($e->getMessage(), 'danger');
        // }
    }

    public function render(int|string|null $id = null): void
    {
        if ($id !== null) {
            try {
                $this->param = $this->userManager->get($id);
            } catch(\Exception $e) {
                // $this->flashMessage($e->getMessage(), 'danger'); // Tohle nebude fungovat
            }
        }

        $this->template->setFile(__DIR__ . '/UserForm.latte');
        $this->template->render();
    }

    public function setUserManager(UserManager $userManager): void
    {
        $this->userManager = $userManager;
    }
}

