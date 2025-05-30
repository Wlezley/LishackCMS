<?php

declare(strict_types=1);

namespace App\Components\Admin;

use App\Components\BaseControl;
use App\Models\Dataset\DatasetManager;
use App\Models\Dataset\Entity\DatasetColumn;
use App\Models\Dataset\Entity\DatasetRow;
use Nette\Application\UI\Form;

class DataEditor extends BaseControl
{
    public const OriginCreate = 'Create';
    public const OriginEdit = 'Edit';

    private string $origin;
    private ?int $datasetId = null;
    private ?int $itemId = null;

    /** @var DatasetManager @inject */
    public DatasetManager $datasetManager;

    /** @var callable(string, int): void */
    public $onSuccess;

    /** @var callable(string): void */
    public $onError;

    protected function createComponentForm(): Form
    {
        if (!isset($this->origin) || !in_array($this->origin, [self::OriginCreate, self::OriginEdit])) {
            throw new \Exception($this->t('error.form.unknown-origin'));
        }

        if (!$this->datasetManager->isReady()) {
            $datasetId = $this->getPresenter()->getParameter('datasetId');

            if ($datasetId && $this->datasetManager->loadDatasetById((int) $datasetId)) {
                $this->datasetId = (int) $datasetId;
            } else {
                throw new \Exception($this->tf('dataset.id.not-found', (int) $datasetId));
            }
        } else {
            $this->datasetId = $this->datasetManager->getDataset()->id;
        }

        $data = null;
        if ($this->origin == self::OriginEdit) {
            $itemId = $this->getPresenter()->getParameter('itemId');

            if ($itemId) {
                $this->itemId = (int) $itemId;
            } else {
                throw new \Exception($this->t('dataset.item-id.not-set'));
            }

            $data = $this->datasetManager->getDataRepository()->findById($this->datasetId, $this->itemId);

            if (!$data) {
                throw new \Exception($this->tf('dataset.item-id.not-found', $this->itemId));
            }
        }

        $form = new Form;

        $form->setHtmlAttribute('autocomplete', 'off');

        $form->addHidden('datasetId')
            ->setValue($this->datasetId);

        $form->addHidden('itemId')
            ->setValue($this->itemId);

        foreach ($this->datasetManager->getColumns() as $c) {
            if ($c->deleted) {
                continue;
            }

            $columnName = "data_{$c->columnId}";
            $input = match ($c->type) {
                'int' => $form->addInteger($columnName, $c->slug),
                'string' => $form->addText($columnName, $c->slug),
                'text' => $form->addTextArea($columnName, $c->slug),
                'wysiwyg' => $form->addTextArea($columnName, $c->slug),
                'bool' => $form->addCheckbox($columnName, $c->slug),
                'json' => $form->addTextArea($columnName, $c->slug),
                'html' => $form->addTextArea($columnName, $c->slug),
                default => null,
            };

            if (!$input) {
                continue;
            }

            $input->setRequired($c->required);

            if ($this->origin == self::OriginEdit && isset($data->values[$c->columnId])) {
                $input->setValue($data->values[$c->columnId]);
            }

            if ($this->origin == self::OriginCreate && isset($c->default)) {
                $input->setDefaultValue($c->default);
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

    /** @param \Nette\Utils\ArrayHash<mixed> $values */
    public function processCreate(Form $form, \Nette\Utils\ArrayHash $values): void
    {
        if (!$this->datasetManager->isReady()) {
            call_user_func($this->onError, $this->t('dataset.id.not-set'));
            return;
        }

        foreach ($this->datasetManager->getColumnsList() as $column) {
            if ($column['required'] && empty($values["data_{$column['columnId']}"])) {
                $label = $column['name'];
                call_user_func($this->onError, $this->tf('error.form.missing-required', $label));
                return;
            }
        }

        $dataRow = new DatasetRow();

        foreach ($values as $key => $value) {
            if (!str_starts_with($key, 'data_')) {
                continue;
            }

            $dataRow->setValue((int) substr($key, 5), $value);
        }

        if (empty($dataRow->getValues())) {
            call_user_func($this->onError, $this->t('error.form.empty-data'));
            return;
        }

        $dataRow = $this->datasetManager->getDataRepository()->insert($this->datasetId, $dataRow);

        if (!$dataRow->id) {
            call_user_func($this->onError, $this->t('dataset.item.not-created'));
            return;
        }

        call_user_func($this->onSuccess, $this->tf('dataset.item.created', $dataRow->id), $this->datasetId);
    }

    /** @param \Nette\Utils\ArrayHash<mixed> $values */
    public function processSave(Form $form, \Nette\Utils\ArrayHash $values): void
    {
        if (!$this->datasetManager->isReady()) {
            call_user_func($this->onError, $this->t('dataset.id.not-set'));
            return;
        }

        foreach ($this->datasetManager->getColumnsList() as $column) {
            if ($column['required'] && empty($values["data_{$column['columnId']}"])) {
                $label = $column['name'];
                call_user_func($this->onError, $this->tf('error.form.missing-required', $label));
                return;
            }
        }

        $dataRow = new DatasetRow();
        $dataRow->id = $values['itemId'] ? (int) $values['itemId']: null;

        foreach ($values as $key => $value) {
            if (!str_starts_with($key, 'data_')) {
                continue;
            }

            $dataRow->setValue((int) substr($key, 5), $value);
        }

        if (empty($dataRow->getValues())) {
            call_user_func($this->onError, $this->t('error.form.empty-data'));
            return;
        }

        $this->datasetManager->getDataRepository()->update($this->datasetId, $dataRow);

        call_user_func($this->onSuccess, $this->tf('dataset.item.saved', $dataRow->id), $this->datasetId);
    }

    public function render(): void
    {
        $this->template->columnList = $this->datasetManager->getColumnsList();

        $this->template->setFile(__DIR__ . '/DataEditor.latte');
        $this->template->render();
    }

    public function setOrigin(string $origin): void
    {
        $this->origin = $origin;
    }

    public function setDatasetManager(DatasetManager $datasetManager): void
    {
        $this->datasetManager = $datasetManager;
    }

    /** @return array<string,string> */
    public function getColumnTypeOptions(): array
    {
        $options = [];
        foreach (DatasetColumn::ALLOWED_TYPES as $type) {
            $options[$type] = $this->t("dataset.column.type.$type");
        }

        return $options;
    }
}
