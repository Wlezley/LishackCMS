<?php

declare(strict_types=1);

namespace App\Commands\User;

use App\Entity\User\UserRepositoryInterface;
use App\Enum\UserRoleEnum;
use App\Models\User\UserRole;
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
    description: 'Change user role for given user. You will be asked to select role.'
)]
final class UserRoleCommand extends Command
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
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
        $username = $input->getArgument('username');
        $role = $input->getArgument('role');

        try {
            $user = $this->userRepository->getByUserName($username);
            Assert::notNull($user, "User '$username' not found.");
        } catch (\Exception $e) {
            $output->writeln(\sprintf('<error>🔴 Error occurred: %s</error>', $e->getMessage()));
            return 1;
        }

        if (!$role) {
            /** @var QuestionHelper $helper */
            $helper = $this->getHelper('question');
            $question = new ChoiceQuestion(
                "Please select role for user '$username': ",
                UserRole::USER_ROLES,
                UserRole::DEFAULT_ROLE
            );
            $role = $helper->ask($input, $output, $question);
        }

        $output->writeln("Changing role of user '$username' to '$role': ...");

        try {
            $role = UserRoleEnum::from($role);
            $user->setRole($role);
            $this->userRepository->save($user);

            $output->writeln(\sprintf("🟢 User '%s' has successfully gain role '%s'", $username, $role->value));
            return 0;
        } catch (\Exception $e) {
            $output->writeln(\sprintf('<error>🔴 Error occurred: %s</error>', $e->getMessage()));
            return 1;
        }
    }
}
