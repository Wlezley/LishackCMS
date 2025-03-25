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

        $form->addText('name', $this->t('login-name'))
            ->setHtmlAttribute('placeholder', $this->t('login-name'))
            ->setHtmlAttribute('autocomplete', 'off')
            ->setHtmlAttribute('readonly', $readOnly)
            ->setValue($param['name'])
            ->setRequired();

        $form->addText('full_name', $this->t('full-name'))
            ->setHtmlAttribute('placeholder', $this->t('full-name'))
            ->setHtmlAttribute('autocomplete', 'off')
            ->setHtmlAttribute('readonly', $readOnly)
            ->setValue($param['full_name'])
            ->setRequired();

        $form->addEmail('email', $this->t('e-mail'))
            ->setHtmlAttribute('placeholder', $this->t('e-mail'))
            ->setHtmlAttribute('autocomplete', 'off')
            ->setHtmlAttribute('readonly', $readOnly)
            ->setValue($param['email']);

        $form->addSelect('role', $this->t('permissions'), $this->getRoleSelectList($param['role']))
            ->setValue($param['role'])
            ->setDisabled($this->readOnlyRole($param['role']))
            ->setRequired();

        $form->addCheckbox('deleted', $this->t('deleted.user'))
            ->setDisabled($readOnly)
            ->setValue($param['deleted']);

        $form->addCheckbox('enabled', $this->t('active.user'))
            ->setDisabled($readOnly)
            ->setValue($param['enabled']);

        if ($this->origin === self::OriginEdit) {
            $form->addCheckbox('change_password', $this->t('password.change'))
                ->setDisabled($readOnly)
                ->setValue(false);
        }

        $form->addPassword('password', $this->t('password'))
            ->setHtmlAttribute('placeholder', $this->t('password'))
            ->setHtmlAttribute('autocomplete', 'new-password')
            ->setRequired($this->origin === self::OriginCreate);

        $form->addPassword('password2', $this->t('password.again'))
            ->setHtmlAttribute('placeholder', $this->t('password.again'))
            ->setHtmlAttribute('autocomplete', 'new-password')
            ->setRequired($this->origin === self::OriginCreate);

        $form->addSubmit('save', $this->origin === self::OriginEdit ? $this->t('save') : $this->t('create'));

        $form->onSuccess[] = [$this, 'process' . $this->origin];

        return $form;
    }

    /** @param \Nette\Utils\ArrayHash<mixed> $values */
    public function processCreate(Form $form, \Nette\Utils\ArrayHash $values): void
    {
        if (empty($values['password'])) {
            call_user_func($this->onError, $this->t('error.form.fill-password'));
            return;
        } elseif ($values['password'] !== $values['password2']) {
            call_user_func($this->onError, $this->t('error.form.passwords-not-match'));
            return;
        }

        if ($this->editorRole->isLessOrEqualsThan($values['role'])) {
            call_user_func($this->onError, $this->t('error.form.user-role-elevation'));
            return;
        }

        try {
            $userID = $this->userManager->create((array)$values);
            call_user_func($this->onSuccess, $this->tf('success.form.user-created', $userID));
        } catch(UserException $e) {
            call_user_func($this->onError, $e->getMessage());
        }
    }

    /** @param \Nette\Utils\ArrayHash<mixed> $values */
    public function processEdit(Form $form, \Nette\Utils\ArrayHash $values): void
    {
        if ($values['change_password']) {
            if ($values['password'] !== $values['password2']) {
                call_user_func($this->onError, $this->t('error.form.passwords-not-match'));
                return;
            }
        }

        // TODO: Check user permissions for role settings, check if not READ-ONLY, etc...

        // if ($this->isReadOnly($values['id'], $values['role'])) {
        //     call_user_func($this->onError, $this->t('error.form.no-permissions.user-edit'));
        //     return;
        // }
        // if ($this->editorRole->isLessOrEqualsThan($values['role'])) {
        //     call_user_func($this->onError, $this->t('error.form.user-role-elevation'));
        //     return;
        // }

        try {
            $userData = UserValidator::prepareData((array)$values);
            $this->userManager->update((int)$values['id'], $userData);
            call_user_func($this->onSuccess, $this->t('success.form.user-saved'));
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
        $USER_ROLES_SELECT = [];
        foreach (UserRole::USER_ROLES as $role_name) {
            $USER_ROLES_SELECT[$role_name] = $this->t('user.role.' . $role_name);
        }

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
