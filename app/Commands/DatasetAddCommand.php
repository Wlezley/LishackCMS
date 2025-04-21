<?php declare(strict_types = 1);

namespace App\Commands;

use App\Models\Dataset\DatasetCreator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(
    name: 'dataset:add',
    description: 'Creates a new dataset TEST template including DB table and config.'
)]
final class DatasetAddCommand extends Command
{
    public function __construct(
        private DatasetCreator $datasetCreator
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('name', InputArgument::REQUIRED, "Dataset name");
        $this->addArgument('col_count', InputArgument::OPTIONAL, "Number of dataset columns");
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<info>Creating new dataset...</info>');

        $datasetName = $input->getArgument('name');

        $datasetId = $this->datasetCreator
            ->configure($datasetName)
            ->addColumn('Column 1')
            ->addColumn('Column 2', '', 'int')
            ->addColumn('Column 3', '', 'bool', true)
            ->addColumn('Column 4', '', 'text')
            ->addColumn('Column 5', '', 'string', true)
            ->addColumn('Column 6', '', 'json', false)
            ->commit();

        // $datasetId = $this->datasetCreator->getDataset()->id;

        $output->writeln('<info>Dataset has been created with ID: ' . $datasetId . '</info>');

        return Command::SUCCESS;
    }
}
