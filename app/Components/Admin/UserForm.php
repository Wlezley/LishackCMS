<?php

declare(strict_types=1);

namespace App\Components\Admin;

use App\Components\BaseControl;
use App\Models\UserException;
use App\Models\UserManager;
use App\Models\UserRole;
use App\Models\UserValidator;
use Nette\Application\UI\Form;

class UserForm extends BaseControl
{
    public const OriginCreate = 'Create';
    public const OriginEdit = 'Edit';

    private string $origin;

    private UserRole $editorRole;

    /** @var callable(string): void */
    public $onSuccess;

    /** @var callable(string): void */
    public $onError;

    public function __construct(
        protected \Nette\Security\User $user,
        protected UserManager $userManager
    ) {
        $this->param = [];
        $this->editorRole = new UserRole($user);
    }

    public function createComponentForm(): Form
    {
        $param = $this->param;

        if (empty($param)) {
            $param = [
                'name' => '',
                'full_name' => '',
                'email' => '',
                'role' => 'user',
                'deleted' => false,
                'enabled' => true,
            ];
        } else {
            unset($param['password']);
            unset($param['password2']);
        }

        $readOnly = false;
        if ($this->origin === self::OriginEdit) {
            $readOnly = $this->isReadOnly($param['id'], $param['role']);
        }

        $form = new Form();

        $form->setHtmlAttribute('autocomplete', 'off');

        if (isset($param['id'])) {
            $form->addHidden('id', $param['id']);
        }

        $form->addText('name', 'Přihlašovací jméno')
            ->setHtmlAttribute('placeholder', 'Přihlašovací jméno')
            ->setHtmlAttribute('autocomplete', 'off')
            ->setHtmlAttribute('readonly', $readOnly)
            ->setValue($param['name'])
            ->setRequired();

        $form->addText('full_name', 'Celé jméno')
            ->setHtmlAttribute('placeholder', 'Celé jméno')
            ->setHtmlAttribute('autocomplete', 'off')
            ->setHtmlAttribute('readonly', $readOnly)
            ->setValue($param['full_name'])
            ->setRequired();

        $form->addEmail('email', 'E-mail')
            ->setHtmlAttribute('placeholder', 'E-mail')
            ->setHtmlAttribute('autocomplete', 'off')
            ->setHtmlAttribute('readonly', $readOnly)
            ->setValue($param['email']);

        $form->addSelect('role', 'Oprávnění', $this->getRoleSelectList($param['role']))
            ->setValue($param['role'])
            ->setDisabled($this->readOnlyRole($param['role']))
            ->setRequired();

        $form->addCheckbox('deleted', 'Smazáno')
            ->setDisabled($readOnly)
            ->setValue($param['deleted']);

        $form->addCheckbox('enabled', 'Aktivní uživatel')
            ->setDisabled($readOnly)
            ->setValue($param['enabled']);

        if ($this->origin === self::OriginEdit) {
            $form->addCheckbox('change_password', 'Změnit heslo')
                ->setDisabled($readOnly)
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
        if (empty($values['password'])) {
            call_user_func($this->onError, 'Vyplňte heslo.');
            return;
        } elseif ($values['password'] !== $values['password2']) {
            call_user_func($this->onError, 'Hesla se neshodují.');
            return;
        }

        if ($this->editorRole->isLessOrEqualsThan($values['role'])) {
            call_user_func($this->onError, 'Uživateli nelze přidelit vyšší nebo stejnou roli, než je ta vaše.');
            return;
        }

        try {
            $userID = $this->userManager->create((array)$values);
            call_user_func($this->onSuccess, "Uživatel byl vytvořen (ID: $userID).");
        } catch(UserException $e) {
            call_user_func($this->onError, $e->getMessage());
        }
    }

    /** @param \Nette\Utils\ArrayHash<mixed> $values */
    public function processEdit(Form $form, \Nette\Utils\ArrayHash $values): void
    {
        if ($values['change_password']) {
            if ($values['password'] !== $values['password2']) {
                call_user_func($this->onError, 'Hesla se neshodují.');
                return;
            }
        }

        // TODO: Check user permissions for role settings, check if not READ-ONLY, etc...

        // if ($this->isReadOnly($values['id'], $values['role'])) {
        //     call_user_func($this->onError, 'Nemáte dostatečná oprávnění pro editaci tohoto uživatele.');
        //     return;
        // }
        // if ($this->editorRole->isLessOrEqualsThan($values['role'])) {
        //     call_user_func($this->onError, 'Uživateli nelze přidelit vyšší nebo stejnou roli, než je ta vaše.');
        //     return;
        // }

        try {
            $userData = UserValidator::prepareData((array)$values);
            $this->userManager->update((int)$values['id'], $userData);
            call_user_func($this->onSuccess, 'Uživatel byl upraven');
        } catch(UserException $e) {
            call_user_func($this->onError, $e->getMessage());
        } catch (\InvalidArgumentException $e) {
            call_user_func($this->onError, $e->getMessage());
        }
    }

    public function render(int|string|null $id = null): void
    {
        try {
            if ($this->origin === self::OriginEdit) {
                if (empty($id)) {
                    throw new \Exception('User ID is missing.');
                }

                $this->param = $this->userManager->get((int) $id);
                $this->template->readOnly = $this->isReadOnly($id, $this->param['role']);
            }
        } catch(\Exception $e) {
            call_user_func($this->onError, $e->getMessage());
        }

        $this->template->setFile(__DIR__ . '/UserForm' . $this->origin . '.latte');
        $this->template->render();
    }

    public function setOrigin(string $origin): void
    {
        $this->origin = $origin;
    }

    private function isReadOnly(int|string $targetId, string $targetRole): bool
    {
        if ($this->user->getId() == $targetId) {
            return false;
        }

        return $this->editorRole->isLessOrEqualsThan($targetRole);
    }

    private function readOnlyRole(?string $targetRole): bool
    {
        if (isset($targetRole) && $this->editorRole->isLessOrEqualsThan($targetRole)) {
            return true;
        }

        return false;
    }

    /** @return array<string,string> */
    private function getRoleSelectList(?string $targetRole): array
    {
        // TODO: Move to Translator
        $USER_ROLES_SELECT = [
            'guest' => 'Host',
            'user' => 'Uživatel',
            'redactor' => 'Redaktor',
            'manager' => 'Moderátor',
            'admin' => 'Správce'
        ];

        if ($this->origin === self::OriginEdit && $this->editorRole->isLessOrEqualsThan($targetRole)) {
            return [$targetRole => $USER_ROLES_SELECT[$targetRole]];
        } else {
            $roleListSelect = [];

            foreach ($this->editorRole->getLowerList() as $roleName) {
                $roleListSelect[$roleName] = $USER_ROLES_SELECT[$roleName];
            }
            return $roleListSelect;
        }
    }
}
