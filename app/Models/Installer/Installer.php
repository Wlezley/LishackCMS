<?php

declare(strict_types=1);

namespace App\Models;

use Nette\Database\Explorer;
use PDO;
use Phinx\Wrapper\TextWrapper;

class Installer
{
    private const TABLE_TO_CHECK = 'cms_config';
    private const SQL_DUMP_FILE = ROOT_DIR_ABSOLUTE . 'db/cms_database_setup.sql';

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
        } catch (\Nette\Database\DriverException $e) {
            return false;
        }
    }

    public function run(): void
    {
        if ($this->isInstalled()) {
            throw new \Exception('CMS is already installed.');
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
    }

    public function getLog(): string
    {
        return $this->log;
    }
}
