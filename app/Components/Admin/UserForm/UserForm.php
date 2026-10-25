<?php

declare(strict_types=1);

namespace App\Components\Admin\UserForm;

use App\Components\BaseControl;
use App\Enum\FormActionType;
use App\Enum\UserRoleEnum;
use App\Exception\UserException;
use App\Models\User\UserRole;
use App\Service\User\UserService;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;
use Webmozart\Assert\Assert;

class UserForm extends BaseControl
{
    private FormActionType $formActionType;

    private UserRole $editorRole;

    /** @var callable(string): void */
    public $onSuccess;

    /** @var callable(string): void */
    public $onError;

    public function __construct(
        private readonly UserService $userService,
    ) {
        $this->param = [];

        Assert::notNull($this->user, 'User must be set');
        $this->editorRole = new UserRole($this->user->getRole()->value);
    }

    public function createComponentForm(): Form
    {
        $form = new Form();

        $form->setHtmlAttribute('autocomplete', 'off');

        unset(
            $this->param['password'],
            $this->param['password2']
        );

        $readOnly = false;
        if ($this->formActionType->isEdit()) {
            Assert::keyExists($this->param, 'id', 'User ID must be set');
            Assert::keyExists($this->param, 'role', 'User role must be set');
            Assert::numeric($this->param['id'], 'User ID must be a number');
            Assert::string($this->param['role'], 'User role must be a string');
            $readOnly = $this->isReadOnly($this->param['id'], $this->param['role']);
        }

        if (isset($this->param['id'])) {
            $form->addHidden('id', $this->param['id']);
        }

        $form->addText('username', $this->t('login-name'))
            ->setHtmlAttribute('placeholder', $this->t('login-name'))
            ->setHtmlAttribute('autocomplete', 'off')
            ->setHtmlAttribute('readonly', $readOnly)
            ->setRequired();

        $form->addText('first_name', $this->t('first-name'))
            ->setHtmlAttribute('placeholder', $this->t('first-name'))
            ->setHtmlAttribute('autocomplete', 'off')
            ->setHtmlAttribute('readonly', $readOnly)
            ->setRequired();

        $form->addText('last_name', $this->t('last-name'))
            ->setHtmlAttribute('placeholder', $this->t('last-name'))
            ->setHtmlAttribute('autocomplete', 'off')
            ->setHtmlAttribute('readonly', $readOnly)
            ->setRequired();

        $form->addEmail('email', $this->t('e-mail'))
            ->setHtmlAttribute('placeholder', $this->t('e-mail'))
            ->setHtmlAttribute('autocomplete', 'off')
            ->setHtmlAttribute('readonly', $readOnly);

        $form->addSelect(
            name: 'role',
            label: $this->t('permissions'),
            items: $this->getRoleSelectList((string) $this->param['role']) // TODO: Use UserRoleEnum
        )
            ->setDisabled($this->readOnlyRole((string) $this->param['role']))
            ->setDefaultValue(UserRoleEnum::User->value)
            ->setRequired();

        $form->addCheckbox('deleted', $this->t('deleted.user'))
            ->setDisabled($readOnly)
            ->setDefaultValue(false);

        $form->addCheckbox('enabled', $this->t('active.user'))
            ->setDisabled($readOnly)
            ->setDefaultValue(true);

        if ($this->formActionType->isEdit()) {
            $form->addCheckbox('change_password', $this->t('password.change'))
                ->setDisabled($readOnly)
                ->setDefaultValue(false);
        }

        $form->addPassword('password', $this->t('password'))
            ->setHtmlAttribute('placeholder', $this->t('password'))
            ->setHtmlAttribute('autocomplete', 'new-password')
            ->setRequired($this->formActionType->isCreate());

        $form->addPassword('password2', $this->t('password.again'))
            ->setHtmlAttribute('placeholder', $this->t('password.again'))
            ->setHtmlAttribute('autocomplete', 'new-password')
            ->setRequired($this->formActionType->isCreate());

        if ($this->formActionType->isCreate()) {
            $form->addSubmit('save', $this->t('create'));
            $form->onSuccess[] = [$this, 'processCreate'];
        } elseif ($this->formActionType->isEdit()) {
            $form->addSubmit('save', $this->t('save'));
            $form->onSuccess[] = [$this, 'processEdit'];
        } else {
            throw new \Exception($this->t('error.form.unknown-origin'));
        }

        return $form;
    }

    /**
     * @param ArrayHash<mixed> $values
     */
    public function processCreate(Form $form, ArrayHash $values): void
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
            $user = $this->userService->create(
                userName: $values['username'],
                email: $values['email'],
                password: $values['password'],
                role: UserRoleEnum::from($values['role']),
                firstName: $values['first_name'],
                lastName: $values['last_name'],
                deleted: (bool) $values['deleted'],
                enabled: (bool) $values['enabled'],
            );
            call_user_func($this->onSuccess, $this->tf('success.form.user-created', $user->getId()));
        } catch (UserException $e) {
            call_user_func($this->onError, $e->getMessage());
        }
    }

    /**
     * @param ArrayHash<mixed> $values
     */
    public function processEdit(Form $form, ArrayHash $values): void
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
//            $userData = UserValidator::prepareData((array)$values);
//            $this->userManager->update((int)$values['id'], $userData);
            call_user_func($this->onSuccess, $this->t('success.form.user-saved'));
        } catch (UserException $e) {
            call_user_func($this->onError, $e->getMessage());
        } catch (\InvalidArgumentException $e) {
            call_user_func($this->onError, $e->getMessage());
        }
    }

    public function render(int|string|null $id = null): void
    {
        try {
            if ($this->formActionType->isEdit()) {
                if (empty($id)) {
                    throw new \Exception('User ID is missing.');
                }

//                $this->param = $this->userManager->get((int) $id);
//                $this->template->readOnly = $this->isReadOnly($id, $this->param['role']);
            }
        } catch (\Exception $e) {
            call_user_func($this->onError, $e->getMessage());
        }

        $this->getTemplate()->setFile(__DIR__ . '/UserForm' . $this->formActionType->value . '.latte');
        $this->getTemplate()->render();
    }

    public function setFormActionType(FormActionType $formActionType): void
    {
        $this->formActionType = $formActionType;
    }

    private function isReadOnly(int|string $targetId, string $targetRole): bool
    {
        if ($this->user?->getId() == $targetId) {
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

    /**
     * @return array<string,string>
     */
    private function getRoleSelectList(string $targetRole): array
    {
        $userRolesSelect = [];
        foreach (UserRole::USER_ROLES as $roleName) {
            $userRolesSelect[$roleName] = $this->t('user.role.' . $roleName);
        }

        if ($this->formActionType->isEdit() && $this->editorRole->isLessOrEqualsThan($targetRole)) {
            return [$targetRole => $userRolesSelect[$targetRole]];
        } else {
            $roleListSelect = [];

            foreach ($this->editorRole->getLowerList() as $roleName) {
                $roleListSelect[$roleName] = $userRolesSelect[$roleName];
            }
            return $roleListSelect;
        }
    }
}
