<?php

declare(strict_types=1);

namespace App\Components\Admin;

use App\Components\BaseControl;
use App\Models\Dataset\DatasetManager;
use App\Models\Dataset\Entity\DatasetColumn;
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

    /** @var callable(string): void */
    public $onSuccess;

    /** @var callable(string): void */
    public $onError;

    protected function createComponentForm(): Form
    {
        if (!isset($this->origin) || !in_array($this->origin, [self::OriginCreate, self::OriginEdit])) {
            throw new \Exception($this->t('error.form.unknown-origin'));
        }

        // CHECK IF THE DATASET READY & SET THE DATASET ID
        if (!$this->datasetManager->isReady()) {
            $datasetId = $this->getPresenter()->getParameter('datasetId');

            if ($datasetId && $this->datasetManager->loadDatasetById((int) $datasetId)) {
                $this->datasetId = (int) $datasetId;
            } else {
                throw new \Exception("Dataset '$datasetId' not found."); // TODO: Translate, improve, etc...
            }
        } else {
            $this->datasetId = $this->datasetManager->getDataset()->id;
        }

        // LOAD COLUMNS SCHEMA
        $columns = $this->datasetManager->getColumnsSchema();

        // CHECK & SET THE ITEM ID
        $data = null;
        if ($this->origin == self::OriginEdit) {
            $itemId = $this->getPresenter()->getParameter('itemId');

            if ($itemId) {
                $this->itemId = (int) $itemId;
            } else {
                throw new \Exception("Dataset item ID doeas not set."); // TODO: Translate, improve, etc...
            }

            // LOAD ITEMS
            $data = $this->datasetManager->getDataRepository()->findById($this->datasetId, $this->itemId);

            // CHECK ITEMS (?)
            if (!$data) {
                throw new \Exception("Dataset item '$itemId' not found."); // TODO: Translate, improve, etc...
            }
        }

        bdump($columns, "Form COLUMNS");
        bdump($data, "Form DATA");
        bdump($this->origin, "Form ORIGIN");

        $form = new Form;

        $form->setHtmlAttribute('autocomplete', 'off');

        $form->addHidden('datasetId')
            ->setValue($this->datasetId);

        // DYNAMIC DATA COLUMNS
        foreach ($columns as $c) {
            if ($c->deleted) {
                continue;
            }

            $columnName = "data_{$c->columnId}";
            $input = match ($c->type) {
                'int' => $form->addInteger($columnName, $c->slug),
                'string' => $form->addText($columnName, $c->slug),
                'text' => $form->addTextArea($columnName, $c->slug),
                'wysiwyg' => $form->addTextArea($columnName, $c->slug)->setHtmlAttribute('class', 'tiny-mce'), // TODO !!!
                'bool' => $form->addCheckbox($columnName, $c->slug),
                'json' => $form->addText($columnName, $c->slug)->setHtmlAttribute('class', 'json-editor'), // TODO !!!
                default => null,
            };

            if (!$input) {
                continue;
            }

            $input->setRequired($c->required);

            if ($this->origin == self::OriginEdit && isset($data->values[$c->columnId])) {
                $input->setValue($data->values[$c->columnId]);
            }

            // TODO: Add column `default` to the table `dataset_column` (and to the DatasetColumn, ColumnRepository, DatasetCreator, DatasetUpdater, DatasetManager, etc.)
            // if ($c->default) {
            //     $input->setDefaultValue($c->default);
            // }
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
        bdump($values, "PROCESS CREATE: VALUES");

        // call_user_func($this->onSuccess, "Položka do datasetu '$datasetId' byla přidána.");
        call_user_func($this->onSuccess, "VYTVOŘENO, ID: 'xyz'."); // DEBUG ONLY !!!
    }

    /** @param \Nette\Utils\ArrayHash<mixed> $values */
    public function processSave(Form $form, \Nette\Utils\ArrayHash $values): void
    {
        bdump($values, "PROCESS SAVE: VALUES");

        // call_user_func($this->onSuccess, "Položka '$itemId' v datasetu '$datasetId' byla uložena.");
        call_user_func($this->onSuccess, "ULOŽENO, ID: 'xyz'."); // DEBUG ONLY !!!
    }

    public function render(): void
    {
        $this->template->columnList = $this->datasetManager->getColumnsList();

        bdump($this->template->columnList, "Form render COLUMNS");

        $this->template->setFile(__DIR__ . '/DataEditor.latte');
        $this->template->render();
    }

    public function setOrigin(string $origin): void
    {
        $this->origin = $origin;
    }

    // public function setDatasetId(int $datasetId): void
    // {
    //     $this->datasetId = $datasetId;
    // }

    // public function setItemId(int $itemId): void
    // {
    //     $this->itemId = $itemId;
    // }

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
