<?php declare(strict_types = 1);

namespace App\Commands;

use App\Models\CategoryManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(
    name: 'category:update',
    description: 'Updates category level data in the database.'
)]
final class CategoryUpdateCommand extends Command
{
    public function __construct(
        private CategoryManager $categoryManager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $output->writeln("Updating category levels ...");
            $this->categoryManager->updateChildLevels();

            $output->writeln('🟢 Categories was updated successfully.');
            return 0;

        } catch (\Exception $e) {
            $output->writeln(\sprintf('<error>🔴 Error occurred: %s</error>', $e->getMessage()));
            return 1;
        }
    }
}
