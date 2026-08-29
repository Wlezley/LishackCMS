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
use Symfony\Component\Console\Question\ChoiceQuestion;
use Webmozart\Assert\Assert;

#[AsCommand(
    name: 'user:role',
    description: 'Change user role for given user. You will be asked to select role.',
    usages: [
        'user:role <username> [<role>]',
    ],
)]
final class UserRoleCommand extends Command
{
    public function __construct(
        private readonly UserService $userService,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('username', InputArgument::REQUIRED, "User's username");
        $this->addArgument('role', InputArgument::OPTIONAL, "User's role");
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $userName = $input->getArgument('username');
        $role = $input->getArgument('role');

        try {
            $user = $this->userService->findByUserName($userName);
            Assert::notNull($user, sprintf("User '%s' not found.", $userName));
        } catch (\Exception $e) {
            $output->writeln(sprintf('<error>🔴 Error occurred: %s</error>', $e->getMessage()));
            return Command::FAILURE;
        }

        if (!$role) {
            /** @var QuestionHelper $helper */
            $helper = $this->getHelper('question');
            $question = new ChoiceQuestion(
                "Please select role for user '$userName': ",
                UserRoleEnum::toArray(),
                UserRoleEnum::User->value,
            );

            $role = $helper->ask($input, $output, $question);
        }

        $output->writeln(sprintf(
            "Changing role of user '%s' to '%s': ...",
            $userName,
            $role
        ));

        try {
            $role = UserRoleEnum::from($role);
            $this->userService->setRole($user, $role);

            $output->writeln(sprintf(
                "🟢 User '%s' has successfully gain role '%s'",
                $userName,
                $role->value
            ));

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln(sprintf('<error>🔴 Error occurred: %s</error>', $e->getMessage()));
            return Command::FAILURE;
        }
    }
}
