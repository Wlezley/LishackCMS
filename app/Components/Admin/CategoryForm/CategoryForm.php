<?php

declare(strict_types=1);

namespace App\Components\Admin;

use App\Components\BaseControl;
use App\Models\ArticleManager;
use App\Models\CategoryException;
use App\Models\CategoryManager;
use App\Models\Helpers\StringHelper;
use Nette\Application\UI\Form;

class CategoryForm extends BaseControl
{
    public const OriginCreate = 'Create';
    public const OriginEdit = 'Edit';

    private string $origin;

    /** @var CategoryManager @inject */
    private CategoryManager $categoryManager;

    /** @var callable(string, int): void */
    public $onSuccess;

    /** @var callable(string): void */
    public $onError;

    public function createComponentForm(): Form
    {
        $param = $this->param;

        $form = new Form();

        $form->setHtmlAttribute('autocomplete', 'off');

        if ($this->origin == self::OriginEdit) {
            $form->addHidden('id', $param['id']);
        }

        $form->addText('name', $this->t('form.category.name'))
            ->setHtmlAttribute('placeholder', '')
            ->setValue($param['name'] ?? '')
            ->setRequired();

        $categorySelectOptions = $this->categoryManager->getCategorySelectData();
        $form->addSelect('parent_id', $this->t('form.category.parent_id'), $categorySelectOptions)
            ->setValue($this->param['parent_id'] ?? ArticleManager::MAIN_CATEGORY_ID)
            ->setRequired();

        $form->addText('name_url', $this->t('form.category.name_url'))
            ->setHtmlAttribute('placeholder', '')
            ->setValue($param['name_url'] ?? '');

        $form->addText('title', $this->t('form.category.title'))
            ->setHtmlAttribute('placeholder', '')
            ->setValue($param['title'] ?? '');

        $form->addText('description', $this->t('form.category.description'))
            ->setHtmlAttribute('placeholder', '')
            ->setValue($param['description'] ?? '');

        $form->addTextArea('body', $this->t('form.category.body'), null, 5)
            ->setHtmlAttribute('placeholder', '')
            ->setValue($param['body'] ?? '');

        $form->addCheckbox('hidden', $this->t('form.category.hidden'))
            ->setValue($param['hidden'] ?? 0);

        $form->addSubmit('save');

        $form->onSuccess[] = [$this, 'process'];

        return $form;
    }

    /** @param \Nette\Utils\ArrayHash<mixed> $values */
    public function process(Form $form, \Nette\Utils\ArrayHash $values): void
    {
        $data = (array)$values;
        $data['hidden'] = $data['hidden'] ? '1' : '0';

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
            if ($this->origin == self::OriginCreate) {
                unset($data['id']);
                $this->categoryManager->create($data);
                call_user_func($this->onSuccess, $this->t('success.form.category-created'), 1);
            } else if ($this->origin == self::OriginEdit) {
                $this->categoryManager->update((int) $data['id'], $data);
                call_user_func($this->onSuccess, $this->t('success.form.category-saved'), $values['page'] ?? 1);
            } else {
                call_user_func($this->onError, $this->t('error.form.unknown-origin'));
                return;
            }
        } catch (CategoryException $e) {
            call_user_func($this->onError, $e->getMessage()); // TODO: Translation based on the error codes
        }
    }

    public function render(): void
    {
        $this->template->setFile(__DIR__ . '/CategoryForm.latte');
        $this->template->render();
    }

    public function setOrigin(string $origin): void
    {
        $this->origin = $origin;
    }

    public function setCategoryManager(CategoryManager $categoryManager): void
    {
        $this->categoryManager = $categoryManager;
    }
}
