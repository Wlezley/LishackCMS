<?php

declare(strict_types=1);

namespace App\Components\Admin;

use App\Components\BaseControl;
use App\Models\UserManager;
use Nette\Application\UI\Form;
use Nette\Bridges\ApplicationLatte\TemplateFactory;

class UserForm extends BaseControl
{
    // /** @var null|array<string,string> $param */
    // protected ?array $param = [];

    /** @var callable(\Nette\Utils\ArrayHash<mixed>): void */
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

        bdump($param, "FORM PARAM");

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
            ->setValue($param['full_name'])
            ->setRequired();

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
        $isEdit = isset($values['id']);

        bdump('SENT', "STATUS");

        if ($isEdit || $values['password'] === $values['password2']) {
            call_user_func($this->onSuccess, $values);
        } else {
            call_user_func($this->onError, 'Hesla se neshodují.');
        }

        // try {
        //     if ($values['password'] === $values['password2']) {
        //         $userID = $this->userManager->create((array)$values);
        //         if ($userID > 1) {
        //             call_user_func($this->onSuccess, $userID);

        //             // $this->flashMessage("Uživatel byl vytvořen (ID: $userID).", 'info');
        //             // $this->redirect('User:default');
        //         }
        //     } else {
        //         call_user_func($this->onError, 'Hesla se neshodují.');

        //         // $this->flashMessage('Hesla se neshodují...', 'danger');
        //     }
        // } catch(\Exception $e) {
        //     bdump($e);
        //     // call_user_func($this->onError, $e->getMessage());
        //     // $this->flashMessage($e->getMessage(), 'danger');
        // }
    }

    public function render(int|string|null $id = null): void
    {
        if ($id !== null) {
            try {
                $this->param = $this->userManager->get($id);
            } catch(\Exception $e) {
                call_user_func($this->onError, $e->getMessage());
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

