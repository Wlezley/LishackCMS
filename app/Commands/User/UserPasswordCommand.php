<?php

declare(strict_types=1);

namespace App\Commands\User;

use App\Service\User\UserService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Webmozart\Assert\Assert;

#[AsCommand(
    name: 'user:pass',
    description: 'Changes the password for the given user. You will be asked to enter a password.',
    usages: [
        'user:pass <username> [<password>]',
    ],
)]
final class UserPasswordCommand extends Command
{
    public function __construct(
        private readonly UserService $userService,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('username', InputArgument::REQUIRED, "User's username");
        $this->addArgument('password', InputArgument::OPTIONAL, "User's password");
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $userName = $input->getArgument('username');
        $newPassword = $input->getArgument('password');

        try {
            $user = $this->userService->findByUserName($userName);
            Assert::notNull($user, sprintf("User '%s' not found.", $userName));
        } catch (\Exception $e) {
            $output->writeln(sprintf('<error>🔴 Error occurred: %s</error>', $e->getMessage()));
            return Command::FAILURE;
        }

        if (!$newPassword) {
            /** @var QuestionHelper $helper */
            $helper = $this->getHelper('question');
            $question = new Question(sprintf("Choose a password for user '%s': ", $userName));
            $newPassword = $helper->ask($input, $output, $question);
        }

        $output->writeln(sprintf("Changing password for user '%s' ...", $userName));

        try {
            $this->userService->setPassword($user, $newPassword);
            $output->writeln(\sprintf("🟢 Password for user '%s' has been successfully changed.", $userName));
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln(\sprintf('<error>🔴 Error occurred: %s</error>', $e->getMessage()));
            return Command::FAILURE;
        }
    }
}
