<?php

declare(strict_types=1);

namespace App\Commands\User;

use App\Enum\UserRoleEnum;
use App\Service\User\UserService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

#[AsCommand(
    name: 'user:add',
    description: 'Adds user with given username to database. You will be asked to enter a password.',
    usages: [
        'user:add <username> [<password>]',
    ],
)]
final class UserAddCommand extends Command
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
        $password = $input->getArgument('password');

        if (!$password) {
            /** @var QuestionHelper $helper */
            $helper = $this->getHelper('question');
            $question = new Question(sprintf("Choose a password for user '%s': ", $userName));
            $password = $helper->ask($input, $output, $question);
        }

        $output->writeln(sprintf("Adding user '%s': ...", $userName));

        try {
            $user = $this->userService->create(
                userName: $userName,
                email: '',
                password: $password,
                role: UserRoleEnum::User,
                firstName: '',
                lastName: '',
                sessionId: '',
            );

            $output->writeln(sprintf('🟢 User has been successfully added; user ID: %d', $user->getId()));
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln(sprintf('<error>🔴 Error occurred: %s</error>', $e->getMessage()));
            return Command::FAILURE;
        }
    }
}
