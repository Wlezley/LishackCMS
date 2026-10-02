<?php

declare(strict_types=1);

namespace App\Components\Admin\DatasetEditor;

use App\Components\BaseControl;
use App\Enum\Dataset\DatasetColumnType;
use App\Exception\DatasetException;
use App\Service\Dataset\DatasetService;
use App\Service\Dataset\DatasetServiceCreator;
use App\Service\Dataset\DatasetServiceUpdater;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;
use Nette\Utils\Json;
use Nette\Utils\JsonException;

class DatasetEditor extends BaseControl
{
    public const string OriginCreate = 'Create';
    public const string OriginEdit = 'Edit';

    private string $origin;

    /** @var DatasetServiceCreator @inject */
    public DatasetServiceCreator $datasetCreator;

    /** @var DatasetService @inject */
    public DatasetService $datasetService;

    /** @var DatasetServiceUpdater @inject */
    public DatasetServiceUpdater $datasetUpdater;

    /** @var callable(string): void */
    public $onSuccess;

    /** @var callable(string): void */
    public $onError;

    protected function createComponentForm(): Form
    {
        $form = new Form();

        $form->setHtmlAttribute('autocomplete', 'off');

        $param = [];
        if ($this->origin == self::OriginEdit && $this->datasetService->isReady()) {
            $param = $this->datasetService->getDataset()->toArray(); // TODO: Move to repository, or create convertible DTO ???
            $param['id'] = $this->datasetService->getDataset()->getId();
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

    /**
     * @param ArrayHash<mixed> $values
     * @throws JsonException
     * @throws DatasetException
     */
    public function processSave(Form $form, ArrayHash $values): void
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

    public function setOrigin(string $origin): void
    {
        $this->origin = $origin;
    }

    public function setDatasetCreator(DatasetServiceCreator $datasetCreator): void
    {
        $this->datasetCreator = $datasetCreator;
    }

    public function setDatasetService(DatasetService $datasetService): void
    {
        $this->datasetService = $datasetService;
    }

    public function setDatasetUpdater(DatasetServiceUpdater $datasetUpdater): void
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
