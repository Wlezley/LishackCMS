<?php

declare(strict_types=1);

namespace App\Components\Admin\CategoryForm;

use App\Components\BaseControl;
use App\Enum\FormActionType;
use App\Exception\CategoryException;
use App\Models\Helpers\StringHelper;
use App\Service\Category\CategoryService;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;
use Webmozart\Assert\Assert;

class CategoryForm extends BaseControl
{
    private FormActionType $formActionType;

    private CategoryService $categoryManager;

    public function __construct(CategoryService $categoryManager)
    {
        $this->categoryManager = $categoryManager;
    }

    /** @var callable(string, int): void */
    public $onSuccess;

    /** @var callable(string): void */
    public $onError;

    public function createComponentForm(): Form
    {
        $form = new Form();

        $form->setHtmlAttribute('autocomplete', 'off');

        $form->addSelect(
            name: 'parent_id',
            label: $this->t('form.category.parent_id'),
            items: $this->categoryManager->getCategorySelectData()
        )
            ->setDefaultValue(CategoryService::MAIN_CATEGORY_ID)
            ->setRequired();

        $form->addText('name', $this->t('form.category.name'))->setRequired();
        $form->addText('name_url', $this->t('form.category.name_url'));
        $form->addText('title', $this->t('form.category.title'));
        $form->addText('description', $this->t('form.category.description'));
        $form->addTextArea('body', $this->t('form.category.body'), null, 5);
        $form->addCheckbox('hidden', $this->t('form.category.hidden'));

        $form->setDefaults($this->param);

        if ($this->formActionType->isEdit()) {
            Assert::keyExists($this->param, 'id', 'Category ID must be set');
            $form->addHidden('id', $this->param['id']);
        }

        $form->addSubmit('save');

        $form->onSuccess[] = [$this, 'process'];

        return $form;
    }

    /** @param ArrayHash<mixed> $values */
    public function process(Form $form, ArrayHash $values): void
    {
        $data = (array)$values;

        if (empty($data['name_url'])) {
            $data['name_url'] = StringHelper::webalize($data['name']);
        }

        if (!StringHelper::isWebalized($data['name_url'])) {
            $data['name_url'] = StringHelper::webalize($data['name_url']);
        }

        $required = [
            ['name' => 'name', 'label.key' => 'form.category.name'],
            ['name' => 'name_url', 'label.key' => 'form.category.name_url'],
            ['name' => 'parent_id', 'label.key' => 'form.category.parent_id'],
        ];

        foreach ($required as $item) {
            if (empty($data[$item['name']])) {
                $label = $this->t($item['label.key']);
                call_user_func($this->onError, $this->tf('error.form.missing-required.settings', $label));
                return;
            }
        }

        try {
            if ($this->formActionType->isCreate()) {
                unset($data['id']);
                $this->categoryManager->create($data);
                call_user_func($this->onSuccess, $this->t('success.form.category-created'), 1);
            } elseif ($this->formActionType->isEdit()) {
                $this->categoryManager->update((int) $data['id'], $data);
                call_user_func($this->onSuccess, $this->t('success.form.category-saved'), $values['page'] ?? 1);
            } else {
                call_user_func($this->onError, $this->t('error.form.unknown-origin'));
                return;
            }
        } catch (CategoryException $e) {
            call_user_func($this->onError, $e->getMessage()); // TODO: Translations
        }
    }

    public function render(): void
    {
        $this->getTemplate()->setFile(__DIR__ . '/CategoryForm.latte');
        $this->getTemplate()->render();
    }

    public function setFormActionType(FormActionType $formActionType): void
    {
        $this->formActionType = $formActionType;
    }
}
