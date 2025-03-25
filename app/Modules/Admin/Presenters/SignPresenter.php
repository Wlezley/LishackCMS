<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presenters;

use App\Components\Admin\SignInFormFactory;
use Nette\Application\UI\Form;

class SignPresenter extends UnsecuredPresenter
{
    /** @var SignInFormFactory @inject */
    public $signInForm;

    public function beforeRender(): void
    {
        $this->redrawControl();
    }

    public function renderIn(): void
    {
        $this->template->title = $this->getPresenterTitle();

        if ($this->user->isLoggedIn()) {
            $this->redirect('Admin:default');
        }
    }

    public function actionOut(): void
    {
        $this->user->logout(true);
        $this->redirect('Sign:in');
    }

    protected function createComponentSignInForm(): Form
    {
        $form = $this->signInForm->create();

        $form->onSuccess[] = function () {
            $this->redirect('Admin:default');
        };

        $form->onError[] = function () {
            $this->flashMessage('Nesprávné přihlašovací údaje', 'danger');
        };

        return $form;
    }
}
