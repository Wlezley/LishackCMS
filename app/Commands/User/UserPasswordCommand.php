<?php

declare(strict_types=1);

namespace App\Commands\User;

use App\Entity\User\UserRepositoryInterface;
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
    description: 'Changes the password for the given user. You will be asked to enter a password.'
)]
final class UserPasswordCommand extends Command
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
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
        $username = $input->getArgument('username');
        $newPassword = $input->getArgument('password');

        try {
            $user = $this->userRepository->getByUserName($username);
            Assert::notNull($user, "User '$username' not found.");
        } catch (\Exception $e) {
            $output->writeln(\sprintf('<error>🔴 Error occurred: %s</error>', $e->getMessage()));
            return 1;
        }

        if (!$newPassword) {
            /** @var QuestionHelper $helper */
            $helper = $this->getHelper('question');
            $question = new Question("Choose a password for user '$username': ");
            $newPassword = $helper->ask($input, $output, $question);
        }

        $output->writeln("Changing password for user '$username' ...");

        try {
            $user->setPasswordFromPlaintext($newPassword);
            $this->userRepository->save($user);

            $output->writeln(\sprintf("🟢 Password for user '%s' has been successfully changed.", $username));
            return 0;
        } catch (\Exception $e) {
            $output->writeln(\sprintf('<error>🔴 Error occurred: %s</error>', $e->getMessage()));
            return 1;
        }
    }
}
