<?php

declare(strict_types=1);

namespace App\Models;

use Nette\Database\ConnectionException;
use Nette\Database\DriverException;
use Nette\Database\Explorer;
use PDO;
use Phinx\Wrapper\TextWrapper;

class Installer
{
    private const TABLE_TO_CHECK = 'cms_config';
    private const SQL_DUMP_FILE = PROJECT_DIR . 'db/cms_database_setup.sql';

    private string $log;

    public function __construct(
        protected Explorer $db,
        private TextWrapper $phinx
    ) {
        $this->log = '';
    }

    public function isInstalled(): bool
    {
        try {
            $result = $this->db->query('SHOW TABLES LIKE ?', self::TABLE_TO_CHECK);
            return $result->getRowCount() === 1;
        } catch (DriverException $e) {
            return false;
        }
    }

    private function isReady(): bool
    {
        try {
            $this->db->getConnection()->getPdo();
        } catch (ConnectionException $e) {
            $this->log = $e->getMessage();
            return false;
        }

        return true;
    }

    public function run(): bool
    {
        if ($this->isInstalled()) {
            throw new \Exception('CMS is already installed.');
        }

        if (!$this->isReady()) {
            throw new \Exception(
                'Database is not properly prepared to the CMS installation.' . PHP_EOL .
                'Please check your configuration files and your database connection first.' . PHP_EOL .
                PHP_EOL .
                'ERROR:' . PHP_EOL .
                $this->log
            );
        }

        if (!file_exists(self::SQL_DUMP_FILE)) {
            throw new \Exception('SQL file not found.');
        }

        $sql = file_get_contents(self::SQL_DUMP_FILE);
        if ($sql === false) {
            throw new \Exception('Error reading SQL file.');
        }

        if (!empty($sql)) {
            $pdo = $this->db->getConnection()->getPdo();
            $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            $pdo->beginTransaction();

            try {
                $pdo->exec($sql);
                $pdo->commit();
            } catch (\PDOException $e) {
                $pdo->rollBack();
                throw new \Exception('SQL file import error: ' . $e->getMessage());
            }
        }

        $this->phinx->setOption('configuration', '../phinx.php');
        $this->log .= $this->phinx->getMigrate();

        return true;
    }

    public function getLog(): string
    {
        return $this->log;
    }
}
