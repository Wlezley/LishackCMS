<?php

declare(strict_types=1);

namespace App\Components\Admin\DatasetEditor;

use App\Components\BaseControl;
use App\Enum\Dataset\DatasetColumnType;
use App\Enum\FormActionType;
use App\Exception\DatasetException;
use App\Service\Dataset\DatasetCreatorService;
use App\Service\Dataset\DatasetService;
use App\Service\Dataset\DatasetUpdaterService;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;
use Nette\Utils\Json;
use Nette\Utils\JsonException;
use Webmozart\Assert\Assert;

class DatasetEditor extends BaseControl
{
    private FormActionType $formActionType;

    /** @var DatasetCreatorService @inject */
    public DatasetCreatorService $datasetCreator;

    /** @var DatasetService @inject */
    public DatasetService $datasetService;

    /** @var DatasetUpdaterService @inject */
    public DatasetUpdaterService $datasetUpdater;

    /** @var callable(string): void */
    public $onSuccess;

    /** @var callable(string): void */
    public $onError;

    protected function createComponentForm(): Form
    {
        $form = new Form();

        $form->setHtmlAttribute('autocomplete', 'off');

        $param = [];
        if ($this->formActionType->isEdit() && $this->datasetService->isReady()) {
            $param = $this->datasetService->getDataset()->toArray(); // TODO: Move to repository, or create convertible DTO ???
            $param['id'] = $this->datasetService->getDataset()->getId();
        }

        $form->addHidden('id');
        $form->addText('name', $this->t('dataset.title'))->setRequired();
        $form->addText('slug', $this->t('slug'));
        $form->addText('component', $this->t('component'));
        $form->addText('presenter', $this->t('presenter'));
        $form->addCheckbox('active', $this->t('active'));
        $form->addCheckbox('deleted', $this->t('deleted'));

        $form->setDefaults($param);

        $form->addHidden('columns', '');
        $form->addSubmit('save', $this->t('save.config'));

        if ($this->formActionType->isCreate()) {
            $form->onSuccess[] = [$this, 'processCreate'];
        } elseif ($this->formActionType->isEdit()) {
            $form->onSuccess[] = [$this, 'processEdit'];
        } else {
            throw new \Exception($this->t('error.form.unknown-origin'));
        }

        return $form;
    }

    /**
     * @param ArrayHash<mixed> $values
     * @throws JsonException
     * @throws DatasetException
     */
    public function processCreate(Form $form, ArrayHash $values): void
    {
        if (empty($values['columns'])) {
            call_user_func($this->onError, $this->t('error.form.empty-data'));
            return;
        }

        $columns = Json::decode($values['columns'], true);

        $this->datasetCreator->configure(
            $values['name'],
            $values['slug'],
            $values['component'],
            $values['presenter'],
            (bool)$values['active'],
            (bool)$values['deleted'],
        );

        foreach ($columns as $c) {
            $columnType = DatasetColumnType::tryFrom($c['type']);
            Assert::notNull($columnType, "Invalid column type: {$c['type']} for column: {$c['name']} in dataset: {$values['name']}");

            $this->datasetCreator->addColumn(
                $c['name'],
                $c['slug'],
                $columnType,
                (bool)$c['required'],
                (bool)$c['listed'],
                (bool)$c['hidden'],
                (bool)$c['deleted'],
                $c['default']
            );
        }

        $datasetId = $this->datasetCreator->commit();

        call_user_func($this->onSuccess, "Dataset ID: $datasetId byl vytvořen.");
    }

    /**
     * @param ArrayHash<mixed> $values
     * @throws JsonException
     * @throws DatasetException
     */
    public function processEdit(Form $form, ArrayHash $values): void
    {
        if (empty($values['columns'])) {
            call_user_func($this->onError, $this->t('error.form.empty-data'));
            return;
        }

        $columns = Json::decode($values['columns'], true);

        $this->datasetUpdater->loadDatasetById((int) $values['id']);

        $this->datasetUpdater->reconfigure(
            $values['name'],
            $values['slug'],
            $values['component'],
            $values['presenter'],
            (bool)$values['active'],
            (bool)$values['deleted'],
        );

        foreach ($columns as $columnId => $column) {
            $columnType = DatasetColumnType::tryFrom($column['type']);
            Assert::notNull($columnType, "Invalid column type: {$column['type']} for column: {$column['name']} in dataset: {$values['name']}");

            $this->datasetUpdater->updateColumn(
                columnId: (int)$column['id'] - 1,
                columnName: $column['name'],
                slug: $column['slug'],
                type: $columnType,
                required: (bool)$column['required'],
                listed: (bool)$column['listed'],
                hidden: (bool)$column['hidden'],
                deleted: (bool)$column['deleted'],
                defaultValue: $column['default']
            );
        }

        $datasetId = $this->datasetUpdater->commit();

        call_user_func($this->onSuccess, "Dataset ID: $datasetId byl uložen.");
    }

    public function render(): void
    {
        $columns = $this->datasetService->getColumnsList();

        if (empty($columns)) {
            $this->template->lastColumnId = 2;

            for ($i = 1; $i <= $this->template->lastColumnId; $i++) {
                $columns[$i] = [
                    'columnId' => $i,
                    'name' => "Data $i",
                    'slug' => "data_$i",
                    'type' => 'string',
                    'required' => false,
                    'listed' => false,
                    'hidden' => false,
                    'deleted' => false,
                    'default' => null,
                ];
            }
        } else {
            $this->template->lastColumnId = $this->datasetService->getLastColumnId();
        }

        $this->template->datasetColumns = $columns;
        $this->template->columnTypeOptions = $this->getColumnTypeOptions();

        $this->getTemplate()->setFile(__DIR__ . '/DatasetEditor.latte');
        $this->getTemplate()->render();
    }

    public function setFormActionType(FormActionType $formActionType): void
    {
        $this->formActionType = $formActionType;
    }

    public function setDatasetCreator(DatasetCreatorService $datasetCreator): void
    {
        $this->datasetCreator = $datasetCreator;
    }

    public function setDatasetService(DatasetService $datasetService): void
    {
        $this->datasetService = $datasetService;
    }

    public function setDatasetUpdater(DatasetUpdaterService $datasetUpdater): void
    {
        $this->datasetUpdater = $datasetUpdater;
    }

    /**
     * @return array<string,string>
     */
    public function getColumnTypeOptions(): array
    {
        $options = [];
        foreach (DatasetColumnType::cases() as $type) {
            $options[$type->value] = $this->t("dataset.column.type.$type->value");
        }

        return $options;
    }
}
