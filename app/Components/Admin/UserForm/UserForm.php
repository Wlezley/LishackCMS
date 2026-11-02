<?php

declare(strict_types=1);

namespace App\Components\Admin\UserForm;

use App\Components\BaseControl;
use App\Dto\User\UserDTO;
use App\Enum\FormActionType;
use App\Enum\UserRoleEnum;
use App\Exception\UserException;
use App\Models\User\UserRole;
use App\Service\User\UserService;
use Nette\Application\UI\Form;
use Nette\Security\User as NetteUser;
use Nette\Utils\ArrayHash;
use Webmozart\Assert\Assert;

class UserForm extends BaseControl
{
    private FormActionType $formActionType;
    private UserRole $editorRole;
    private int $userId;
    private bool $readOnly = true;

    /** @var callable(string): void */
    public $onSuccess;
    /** @var callable(string): void */
    public $onError;

    public function __construct(
        private readonly UserService $userService,
        private readonly NetteUser $editorUser,
    ) {
        $this->editorRole = new UserRole($this->editorUser);
    }

    public function createComponentForm(): Form
    {
        $form = new Form();

        $form->setHtmlAttribute('autocomplete', 'off');

        $this->fillParams();

        $form->addText('userName', $this->t('login-name'))
            ->setHtmlAttribute('placeholder', $this->t('login-name'))
            ->setHtmlAttribute('autocomplete', 'off')
            ->setHtmlAttribute('readonly', $this->readOnly)
            ->setRequired();

        $form->addText('firstName', $this->t('first-name'))
            ->setHtmlAttribute('placeholder', $this->t('first-name'))
            ->setHtmlAttribute('autocomplete', 'off')
            ->setHtmlAttribute('readonly', $this->readOnly)
            ->setRequired();

        $form->addText('lastName', $this->t('last-name'))
            ->setHtmlAttribute('placeholder', $this->t('last-name'))
            ->setHtmlAttribute('autocomplete', 'off')
            ->setHtmlAttribute('readonly', $this->readOnly)
            ->setRequired();

        $form->addEmail('email', $this->t('e-mail'))
            ->setHtmlAttribute('placeholder', $this->t('e-mail'))
            ->setHtmlAttribute('autocomplete', 'off')
            ->setHtmlAttribute('readonly', $this->readOnly);

        // TODO: Remove - Debug ONLY !!!
        bdump($this->param, 'PARAM');
        bdump($this->editorRole, 'EDITOR ROLE');
        bdump($this->editorUser, 'EDITOR USER');

        $form->addSelect(
            name: 'role',
            label: $this->t('permissions'),
            items: $this->getRoleSelectList(),
        )
//            ->setDisabled($this->readOnlyRole((string) $this->param['role']))
//            ->setDefaultValue(UserRoleEnum::User->value)
            ->setRequired();

        $form->addCheckbox('deleted', $this->t('deleted.user'))
            ->setDisabled($this->readOnly)
            ->setDefaultValue(false);

        $form->addCheckbox('enabled', $this->t('active.user'))
            ->setDisabled($this->readOnly)
            ->setDefaultValue(true);

        if ($this->formActionType->isEdit()) {
            $form->addCheckbox('change_password', $this->t('password.change'))
                ->setDisabled($this->readOnly)
                ->setDefaultValue(false);

            $form->setDefaults($this->param);
        }

        $form->addPassword('password', $this->t('password'))
            ->setHtmlAttribute('placeholder', $this->t('password'))
            ->setHtmlAttribute('autocomplete', 'new-password')
            ->setRequired($this->formActionType->isCreate());

        $form->addPassword('password2', $this->t('password.again'))
            ->setHtmlAttribute('placeholder', $this->t('password.again'))
            ->setHtmlAttribute('autocomplete', 'new-password')
            ->setRequired($this->formActionType->isCreate());

        if (isset($this->param['id'])) {
            $form->addHidden('id', $this->param['id']);
        }

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

    // TODO: Check and Rework...
    public function buildFormItem(): void
    {
        $this->template->readOnly = $this->readOnly;
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
                userName: $values['userName'],
                email: $values['email'],
                password: $values['password'],
                role: UserRoleEnum::from($values['role']),
                firstName: $values['firstName'],
                lastName: $values['lastName'],
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
        if ($this->isReadOnly((int) $values['id'], UserRoleEnum::tryFromOrDefault($values['role']))) {
            call_user_func($this->onError, $this->t('error.form.no-permissions.user-edit'));
            return;
        }
        if ($this->editorRole->isLessOrEqualsThan($values['role'])) {
            call_user_func($this->onError, $this->t('error.form.user-role-elevation'));
            return;
        }

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

    public function setUserId(int $userId): void
    {
        $this->userId = $userId;
    }

    private function fillParams(): void
    {
        unset(
            $this->param['password'],
            $this->param['password2']
        );


        $this->readOnly = false;
        if ($this->formActionType->isEdit()) {
            $user = $this->userService->findByUserId($this->userId);
            Assert::notNull($user, 'User not found'); // TODO: Use translated message

            $userDto = UserDTO::fromEntity($user);
            $this->param = $userDto->toArray();

            $this->readOnly = $this->isReadOnly(
                $user->getId(),
                $user->getRole(),
            );
        }
    }

    private function isReadOnly(int $targetId, UserRoleEnum $targetRole): bool
    {
        if ($this->editorUser->getId() === $targetId) {
            return false;
        }

        return $this->editorRole->isLessOrEqualsThan($targetRole->value);
    }

    // TODO: Remove or rework
//    private function readOnlyRole(?string $targetRole): bool
//    {
////        if (isset($targetRole) && $this->editorRole->isLessOrEqualsThan($targetRole)) {
////            return true;
////        }
//
//        return false;
//    }

    /**
     * @return array<string,string>
     * @todo: Move to RoleService?
     */
    private function getRoleSelectList(): array
    {
        $roleListSelect = [];
        foreach ($this->editorRole->getLowerList() as $roleName) {
            $roleListSelect[$roleName] = $this->t('user.role.' . $roleName);
        }

        if ($this->editorRole->is(UserRoleEnum::Admin->value)) {
            $roleListSelect[UserRoleEnum::Admin->value] = $this->t('user.role.' . UserRoleEnum::Admin->value);
        }

        return $roleListSelect;
    }
}
