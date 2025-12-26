<?php

declare(strict_types=1);

namespace App\Components\Admin;

use App\Models\Translation\Translator;
use Nette\Application\UI\Form;
use Nette\Security\User;

class SignInFormFactory
{
    public function __construct(
        protected User $user,
        protected Translator $translator,
    ) {
    }

    public function create(): Form
    {
        $form = new Form();

        $form->addText('username', $this->translator->translate('login-name'))
            ->setHtmlAttribute('placeholder', $this->translator->translate('login-name'))
            ->setRequired();

        $form->addPassword('password', $this->translator->translate('password'))
            ->setHtmlAttribute('placeholder', $this->translator->translate('password'))
            ->setRequired();

        $rememberLabel = \Nette\Utils\Html::el('span')
            ->setAttribute('class', 'form-check-label')
            ->setAttribute('style', 'font-size: 17px;') // TODO: css ...
            ->setText($this->translator->translate('login.remember'));

        $form->addCheckbox('remember', $rememberLabel)
            ->setHtmlAttribute('class', 'form-check-input me-2 p-2');

        $form->addSubmit('login', $this->translator->translate('btn.login'));

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
                $this->user->setExpiration('12 hours');
            }
        } catch (\Nette\Security\AuthenticationException $e) {
            $form->addError($e->getMessage());
        }
    }
}
