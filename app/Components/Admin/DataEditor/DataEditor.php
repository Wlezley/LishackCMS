<?php

declare(strict_types=1);

namespace App\Components\Admin\DataEditor;

use App\Components\BaseControl;
use App\Exception\DatasetException;
use App\Service\Dataset\DatasetService;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;
use Webmozart\Assert\Assert;

class DataEditor extends BaseControl
{
    public const string OriginCreate = 'Create';
    public const string OriginEdit = 'Edit';

    private string $origin;
    private ?int $datasetId = null;
    private ?int $itemId = null;

    /** @var DatasetService @inject */
    public DatasetService $datasetService;

    /** @var callable(string, int): void */
    public $onSuccess;

    /** @var callable(string): void */
    public $onError;

    protected function createComponentForm(): Form
    {
        if (!isset($this->origin) || !in_array($this->origin, [self::OriginCreate, self::OriginEdit])) {
            throw new \Exception($this->t('error.form.unknown-origin'));
        }

        if (!$this->datasetService->isReady()) {
            $datasetId = $this->getPresenter()->getParameter('datasetId');

            if ($datasetId && $this->datasetService->loadDatasetById((int) $datasetId)) {
                $this->datasetId = (int) $datasetId;
            } else {
                throw new \Exception($this->tf('dataset.id.not-found', (int) $datasetId));
            }
        } else {
            $this->datasetId = $this->datasetService->getDataset()->getId();
        }

        Assert::notNull($this->datasetId);

        $data = null;
        if ($this->origin == self::OriginEdit) {
            $itemId = $this->getPresenter()->getParameter('itemId');

            if ($itemId) {
                $this->itemId = (int) $itemId;
            } else {
                throw new \Exception($this->t('dataset.item-id.not-set'));
            }

            $data = $this->datasetService->getDataRepository()->findById($this->datasetId, $this->itemId);

            if (!$data) {
                throw new \Exception($this->tf('dataset.item-id.not-found', $this->itemId));
            }
        }

        $form = new Form();

        $form->setHtmlAttribute('autocomplete', 'off');

        $form->addHidden('datasetId')
            ->setValue($this->datasetId);

        $form->addHidden('itemId')
            ->setValue($this->itemId);

        foreach ($this->datasetService->getColumns() as $c) {
            if ($c->isDeleted()) {
                continue;
            }

            $columnName = "data_{$c->getId()}";
            $input = match ($c->getType()->value) {
                'int' => $form->addInteger($columnName, $c->getSlug()),
                'string' => $form->addText($columnName, $c->getSlug()),
                'text' => $form->addTextArea($columnName, $c->getSlug()),
                'wysiwyg' => $form->addTextArea($columnName, $c->getSlug()),
                'bool' => $form->addCheckbox($columnName, $c->getSlug()),
                'json' => $form->addTextArea($columnName, $c->getSlug()),
                'html' => $form->addTextArea($columnName, $c->getSlug()),
                default => null,
            };

            if (!$input) {
                continue;
            }

            $input->setRequired($c->isRequired());

            if ($this->origin == self::OriginEdit && isset($data['data_' . $c->getId()])) {
                $input->setValue($data['data_' . $c->getId()]);
            }

            if ($this->origin == self::OriginCreate && $c->getDefaultValue() !== null) {
                $input->setDefaultValue($c->getDefaultValue());
            }
        }

        if ($this->origin == self::OriginCreate) {
            $form->addSubmit('save', $this->t('create'));
            $form->onSuccess[] = [$this, 'processCreate'];
        } elseif ($this->origin == self::OriginEdit) {
            $form->addSubmit('save', $this->t('save'));
            $form->onSuccess[] = [$this, 'processSave'];
        }

        return $form;
    }

    /**
     * @param ArrayHash<mixed> $values
     */
    public function processCreate(Form $form, ArrayHash $values): void
    {
        if (!$this->datasetService->isReady() || $this->datasetId === null) {
            call_user_func($this->onError, $this->t('dataset.id.not-set'));
            return;
        }

        foreach ($this->datasetService->getColumnsList() as $column) {
            if ($column['required'] && empty($values["data_{$column['columnId']}"])) {
                $label = $column['name'];
                call_user_func($this->onError, $this->tf('error.form.missing-required', $label));
                return;
            }
        }

        $dataRow = [];

        foreach ($values as $key => $value) {
            if (!str_starts_with($key, 'data_')) {
                continue;
            }

            $dataRow[$key] = $value;
        }

        if (empty($dataRow)) {
            call_user_func($this->onError, $this->t('error.form.empty-data'));
            return;
        }

        $id = $this->datasetService->getDataRepository()->insert($this->datasetId, $dataRow);
        if ($id === 0) {
            call_user_func($this->onError, $this->t('dataset.item.not-created'));
            return;
        }

        call_user_func($this->onSuccess, $this->tf('dataset.item.created', $id), $this->datasetId);
    }

    /**
     * @param ArrayHash<mixed> $values
     * @throws DatasetException
     */
    public function processSave(Form $form, ArrayHash $values): void
    {
        if (!$this->datasetService->isReady() || $this->datasetId === null) {
            call_user_func($this->onError, $this->t('dataset.id.not-set'));
            return;
        }

        foreach ($this->datasetService->getColumnsList() as $column) {
            if ($column['required'] && empty($values["data_{$column['columnId']}"])) {
                $label = $column['name'];
                call_user_func($this->onError, $this->tf('error.form.missing-required', $label));
                return;
            }
        }

        $dataRow = [];
        $itemId = $values['itemId'] ? (int) $values['itemId'] : null;

        foreach ($values as $key => $value) {
            if (!str_starts_with($key, 'data_')) {
                continue;
            }

            $dataRow[$key] = $value;
        }

        if (empty($dataRow)) {
            call_user_func($this->onError, $this->t('error.form.empty-data'));
            return;
        }

        Assert::notNull($itemId);
        $this->datasetService->getDataRepository()->update($this->datasetId, $itemId, $dataRow);

        call_user_func($this->onSuccess, $this->tf('dataset.item.saved', $itemId), $this->datasetId);
    }

    public function render(): void
    {
        $this->template->columnList = $this->datasetService->getColumnsList();

        $this->getTemplate()->setFile(__DIR__ . '/DataEditor.latte');
        $this->getTemplate()->render();
    }

    public function setOrigin(string $origin): void
    {
        $this->origin = $origin;
    }

    public function setDatasetService(DatasetService $datasetService): void
    {
        $this->datasetService = $datasetService;
    }

    /** @return array<string,string> */
    public function getColumnTypeOptions(): array
    {
        $options = [];
        foreach (\App\Enum\Dataset\DatasetColumnType::cases() as $type) {
            $options[$type->value] = $this->t("dataset.column.type.{$type->value}");
        }

        return $options;
    }
}
