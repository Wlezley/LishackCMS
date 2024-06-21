<?php

declare(strict_types=1);

namespace App\Components\Admin;

use Nette;
use Nette\Security\User;
use Nette\Application\UI\Form;


class SignInFormFactory
{
    /** @var User */
    protected $user;

    public function __construct(User $user)
    {
        $this->user = $user;
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

        $form->addSubmit('send', 'Přihlásit se');

        $form->onSuccess[] = [$this, 'process'];

        return $form;
    }

    public function process(Form $form, $values)
    {
        try {
            $this->user->login($values->username, $values->password);
            // $this->user->setExpiration('+6 hours');
            $this->user->setExpiration(null);
        } catch(Nette\Security\AuthenticationException $e) {
            $form->addError($e->getMessage());
            bdump($e, "error");
        }
    }
}

