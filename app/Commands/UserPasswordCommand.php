<?php declare(strict_types = 1);

namespace App\Commands;

use App\Models\UserException;
use App\Models\UserManager;
use App\Models\UserValidator;
use Nette\Database\Explorer;
use Nette\Security\Passwords;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Question\Question;

#[AsCommand(
    name: 'user:pass',
    description: 'Changes the password for the given user. You will be asked to enter a password.'
)]
final class UserPasswordCommand extends Command
{
    public function __construct(
        private Explorer $db,
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
        $newPassword = $input->getArgument('password');

        try {
            $userId = $this->userManager->getIdByName($username);
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
            // Do NOT use UserManager::setPassword() here,
            // because it will reject handle admin accounts.

            $user = $this->db->table(UserManager::TABLE_NAME)
                ->get($userId);

            if (!$user) {
                throw new UserException("User ID '$userId' not found.");
            }

            $data = ['password' => (new Passwords(PASSWORD_BCRYPT, ['cost' => 12]))->hash($newPassword)];
            UserValidator::validateData($data);
            $user->update($data);

            $output->writeln(\sprintf("🟢 Password for user '%s' has been successfully changed.", $username));
            return 0;

        } catch (\Exception $e) {
            $output->writeln(\sprintf('<error>🔴 Error occurred: %s</error>', $e->getMessage()));
            return 1;
        }
    }
}
