<?php

declare(strict_types=1);

namespace App\Components\Admin;

use Nette\Security\User;
use Nette\Application\UI\Form;

class SignInFormFactory
{
    public function __construct(protected User $user)
    {
    }

    public function create(): Form
    {
        $form = new Form();

        $form->addText('username', 'Přihlašovací jméno')
            ->setHtmlAttribute('placeholder', 'Přihlašovací jméno')
            ->setRequired();

        $form->addPassword('password', 'Heslo')
            ->setHtmlAttribute('placeholder', 'Heslo')
            ->setRequired();

        $form->addCheckbox('remember');

        $form->addSubmit('login', 'Přihlásit se');

        $form->onSuccess[] = [$this, 'process'];

        return $form;
    }

    /** @param \Nette\Utils\ArrayHash<mixed> $values */
    public function process(Form $form, \Nette\Utils\ArrayHash $values): void
    {
        try {
            $this->user->login($values->username, $values->password);

            if ($values->remember) {
                $this->user->setExpiration('7 days');
            } else {
                $this->user->setExpiration('1 hour');
            }

        } catch(\Nette\Security\AuthenticationException $e) {
            $form->addError($e->getMessage());
        }
    }
}

