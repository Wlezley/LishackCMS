<?php

declare(strict_types=1);

namespace App\Commands\Dataset;

use App\Enum\Dataset\DatasetColumnType;
use App\Exception\DatasetException;
use App\Service\Dataset\DatasetServiceCreator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'dataset:add',
    description: 'Creates a new dataset TEST template including DB table and config.'
)]
final class DatasetAddCommand extends Command
{
    public function __construct(
        private readonly DatasetServiceCreator $datasetCreator
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('name', InputArgument::REQUIRED, 'Dataset name');
        $this->addArgument('col_count', InputArgument::OPTIONAL, 'Number of dataset columns');
    }

    /**
     * @throws DatasetException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<info>Creating new dataset...</info>');

        $datasetName = $input->getArgument('name');

        $datasetId = $this->datasetCreator
            ->configure($datasetName)
            ->addColumn('Column 1')
            ->addColumn('Column 2', '', DatasetColumnType::Int)
            ->addColumn('Column 3', '', DatasetColumnType::Bool, true)
            ->addColumn('Column 4', '', DatasetColumnType::Text)
            ->addColumn('Column 5', '', DatasetColumnType::String, true)
            ->addColumn('Column 6', '', DatasetColumnType::Json, false)
            ->commit();

        // $datasetId = $this->datasetCreator->getDataset()->id;

        $output->writeln('<info>Dataset has been created with ID: ' . $datasetId . '</info>');

        return Command::SUCCESS;
    }
}
