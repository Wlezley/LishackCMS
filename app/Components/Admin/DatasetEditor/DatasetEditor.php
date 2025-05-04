<?php

declare(strict_types=1);

namespace App\Components\Admin;

use App\Components\BaseControl;
use App\Models\Dataset\DatasetCreator;
use App\Models\Dataset\DatasetManager;
use App\Models\Dataset\DatasetUpdater;
use App\Models\Dataset\Entity\DatasetColumn;
use Nette\Application\UI\Form;
use Nette\Utils\Json;

class DatasetEditor extends BaseControl
{
    public const OriginCreate = 'Create';
    public const OriginEdit = 'Edit';

    private string $origin;

    /** @var DatasetCreator @inject */
    public DatasetCreator $datasetCreator;

    /** @var DatasetManager @inject */
    public DatasetManager $datasetManager;

    /** @var DatasetUpdater @inject */
    public DatasetUpdater $datasetUpdater;

    /** @var callable(string): void */
    public $onSuccess;

    /** @var callable(string): void */
    public $onError;

    protected function createComponentForm(): Form
    {
        $form = new Form;

        $form->setHtmlAttribute('autocomplete', 'off');

        $param = [];
        if ($this->origin == self::OriginEdit && $this->datasetManager->isReady()) {
            $param = $this->datasetManager->getDataset()->toDatabaseRow();
            $param['id'] = $this->datasetManager->getDataset()->id;
        }

        $form->addHidden('id')
            ->setValue($param['id'] ?? null);

        $form->addText('name', $this->t('dataset.title'))
            ->setValue($param['name'] ?? '')
            ->setRequired();

        $form->addText('slug', $this->t('slug'))
            ->setValue($param['slug'] ?? '');

        $form->addText('component', $this->t('component'))
            ->setValue($param['component'] ?? '');

        $form->addText('presenter', $this->t('presenter'))
            ->setValue($param['presenter'] ?? '');

        $form->addCheckbox('active', $this->t('active'))
            ->setValue($param['active'] ?? true);

        $form->addCheckbox('deleted', $this->t('deleted'))
            ->setValue($param['deleted'] ?? false);

        $form->addHidden('columns', '');
        $form->addSubmit('save', $this->t('save.config'));

        if ($this->origin == self::OriginCreate) {
            $form->onSuccess[] = [$this, 'processCreate'];
        } elseif ($this->origin == self::OriginEdit) {
            $form->onSuccess[] = [$this, 'processSave'];
        } else {
            throw new \Exception($this->t('error.form.unknown-origin'));
        }

        return $form;
    }

    /** @param \Nette\Utils\ArrayHash<mixed> $values */
    public function processCreate(Form $form, \Nette\Utils\ArrayHash $values): void
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
            $values['active'],
            $values['deleted']
        );

        foreach ($columns as $c) {
            $this->datasetCreator->addColumn(
                $c['name'],
                $c['slug'],
                $c['type'],
                $c['required'],
                $c['listed'],
                $c['hidden'],
                $c['deleted'],
                $c['default']
            );
        }

        $datasetId = $this->datasetCreator->commit();

        call_user_func($this->onSuccess, "Dataset ID: $datasetId byl vytvořen.");
    }

    /** @param \Nette\Utils\ArrayHash<mixed> $values */
    public function processSave(Form $form, \Nette\Utils\ArrayHash $values): void
    {
        if (empty($values['columns'])) {
            call_user_func($this->onError, $this->t('error.form.empty-data'));
            return;
        }

        $columns = Json::decode($values['columns'], true);

        $this->datasetUpdater->loadDatasetById((int) $values['id']);

        $this->datasetUpdater->configure(
            $values['name'],
            $values['slug'],
            $values['component'],
            $values['presenter'],
            $values['active'],
            $values['deleted']
        );

        foreach ($columns as $columnId => $c) {
            $this->datasetUpdater->updateColumn(
                $columnId,
                $c['name'],
                $c['slug'],
                $c['type'],
                $c['required'],
                $c['listed'],
                $c['hidden'],
                $c['deleted'],
                $c['default']
            );
        }

        $datasetId = $this->datasetUpdater->commit();

        call_user_func($this->onSuccess, "Dataset ID: $datasetId byl uložen.");
    }

    public function render(): void
    {
        $columns = $this->datasetManager->getColumnsList();

        if (empty($columns)) {
            $this->template->lastColumnId = 2;

            for ($i=1; $i <= $this->template->lastColumnId; $i++) {
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
            $this->template->lastColumnId = $this->datasetManager->getLastColumnId();
        }

        $this->template->datasetColumns = $columns;
        $this->template->columnTypeOptions = $this->getColumnTypeOptions();

        $this->template->setFile(__DIR__ . '/DatasetEditor.latte');
        $this->template->render();
    }

    public function setOrigin(string $origin): void
    {
        $this->origin = $origin;
    }

    public function setDatasetCreator(DatasetCreator $datasetCreator): void
    {
        $this->datasetCreator = $datasetCreator;
    }

    public function setDatasetManager(DatasetManager $datasetManager): void
    {
        $this->datasetManager = $datasetManager;
    }

    public function setDatasetUpdater(DatasetUpdater $datasetUpdater): void
    {
        $this->datasetUpdater = $datasetUpdater;
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
