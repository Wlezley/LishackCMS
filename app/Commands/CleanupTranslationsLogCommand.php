<?php

declare(strict_types=1);

namespace App\Commands;

use App\Models\Translation\TranslatorMaintenanceManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Webmozart\Assert\Assert;

#[AsCommand(
    name: 'translations:cleanup-log',
    description: 'Removes resolved records from translations_log.',
)]
final class CleanupTranslationsLogCommand extends Command
{
    public function __construct(
        private readonly TranslatorMaintenanceManager $translatorCommandManager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output,
    ): int {
        try {
            $output->writeln('Cleaning up resolved translations log records ...');
            $deletedRows = $this->translatorCommandManager->cleanupTranslationsLog();
            Assert::integer($deletedRows, 'Nothing to delete.');

            $output->writeln(sprintf(
                '<info>🟢 Deleted %d log records.</info>',
                $deletedRows,
            ));

            return self::SUCCESS;
        } catch (\InvalidArgumentException $e) {
            $output->writeln("<warning>{$e->getMessage()}</warning>");
            $output->writeln('<info>Nothing to delete.</info>');
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $output->writeln(sprintf(
                '<error>🔴 Error occurred: %s</error>',
                $e->getMessage(),
            ));

            return self::FAILURE;
        }
    }
}
