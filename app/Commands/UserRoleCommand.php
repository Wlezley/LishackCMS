<?php declare(strict_types = 1);

namespace App\Commands;

use App\Models\UserException;
use App\Models\UserManager;
use App\Models\UserRole;
use App\Models\UserValidator;
use Nette\Database\Explorer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Question\ChoiceQuestion;

#[AsCommand(
    name: 'user:role',
    description: 'Change user role for given user. You will be asked to select role.'
)]
final class UserRoleCommand extends Command
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
        $this->addArgument('role', InputArgument::OPTIONAL, "User's role");
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $username = $input->getArgument('username');
        $role = $input->getArgument('role');

        try {
            $userId = $this->userManager->getIdByName($username);
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
            // Do NOT use UserManager::setPassword() here,
            // because it will reject handle admin accounts.

            $user = $this->db->table(UserManager::TABLE_NAME)
                ->get($userId);

            if (!$user) {
                throw new UserException("User ID '$userId' not found.");
            }

            $data = ['role' => $role];
            UserValidator::validateData($data);
            $user->update($data);

            $output->writeln(\sprintf("🟢 User '%s' has successfully gain role '%s'", $username, $role));
            return 0;

        } catch (\Exception $e) {
            $output->writeln(\sprintf('<error>🔴 Error occurred: %s</error>', $e->getMessage()));
            return 1;
        }
    }
}
