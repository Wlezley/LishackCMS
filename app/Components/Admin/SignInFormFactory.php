<?php

declare(strict_types=1);

namespace App\Components\Admin;

use Nette;
use Nette\Security\User;
use Nette\Application\UI\Form;


class SignInFormFactory
{
    public function __construct(protected User $user)
    {
    }

    public function create()
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

    public function process(Form $form, $values)
    {
        try {
            $this->user->login($values->username, $values->password);

            // bdump(ini_get('session.gc_maxlifetime') / 60 / 60 / 24);

            if ($values->remember) {
                $this->user->setExpiration('7 days');
            } else {
                $this->user->setExpiration('1 hour');
            }

        } catch(Nette\Security\AuthenticationException $e) {
            $form->addError($e->getMessage());
        }
    }
}

