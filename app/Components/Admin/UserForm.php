<?php

declare(strict_types=1);

namespace App\Components\Admin;

use App\Components\BaseControl;
use App\Models\UserManager;
use Nette\Application\UI\Form;

class UserForm extends BaseControl
{
    /** @var array<string, string> $userData */
    private array $userData;

    public function __construct(
        protected \Nette\Security\User $user,
        protected UserManager $userManager
    ) {
        $this->userData = [];
    }

    public function createComponentForm(): Form
    {
        $form = new Form();

        $form->setHtmlAttribute('autocomplete', 'off');
        // $form->getElementPrototype()->method('POST');

        $form->addText('username', 'Přihlašovací jméno')
            ->setHtmlAttribute('placeholder', 'Přihlašovací jméno')
            ->setRequired();

        $form->addText('full_name', 'Celé jméno')
            ->setHtmlAttribute('placeholder', 'Celé jméno')
            ->setRequired();

        $form->addPassword('password', 'Heslo')
            ->setHtmlAttribute('placeholder', 'Heslo')
            ->setHtmlAttribute('autocomplete', 'new-password')
            ->setRequired();

        $form->addPassword('password2', 'Heslo znovu')
            ->setHtmlAttribute('placeholder', 'Heslo znovu')
            ->setHtmlAttribute('autocomplete', 'new-password')
            ->setRequired();

        $form->addCheckbox('remember')
            ->setCaption('Test');

        $form->addSubmit('save', 'Přihlásit se');

        $form->onSuccess[] = [$this, 'process'];

        return $form;
    }

    /** @param \Nette\Utils\ArrayHash<mixed> $values */
    public function process(Form $form, \Nette\Utils\ArrayHash $values): void
    {
        try {
            $id = 1;
            $this->userManager->load($id);
            $this->userData = $this->userManager->getData();

            bdump($this->userData);
            bdump($values);

        } catch(\Exception $e) {
            $form->addError($e->getMessage());
            bdump($e, "error");
        }
    }

    public function render(?int $id = null): void
    {
        if ($id !== null) {
            try {
                $this->userManager->load($id);
                $this->userData = $this->userManager->getData();
                $this->template->userData = $this->userData;
            } catch(\Exception $e) {
                bdump($e, "error");
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

