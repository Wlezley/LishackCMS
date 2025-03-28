<?php declare(strict_types = 1);

namespace App\Commands;

use App\Models\UserManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Question\Question;

#[AsCommand(
    name: 'user:add',
    description: 'Adds user with given username to database. You will be asked to enter a password.'
)]
final class UserAddCommand extends Command
{
    public function __construct(
        private UserManager $userManager
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
        $password = $input->getArgument('password');

        if (!$password) {
            /** @var QuestionHelper $helper */
            $helper = $this->getHelper('question');
            $question = new Question("Choose a password for user '$username': ");
            $password = $helper->ask($input, $output, $question);
        }

        $output->writeln("Adding user '$username': ...");

        try {
            $userId = $this->userManager->create([
                'name' => $username,
                'password' => $password,
            ]);
            $output->writeln(\sprintf('🟢 User has been successfully added; user ID: %d', $userId));
            return 0;

        } catch (\Exception $e) {
            $output->writeln(\sprintf('<error>🔴 Error occurred: %s</error>', $e->getMessage()));
            return 1;
        }
    }
}
