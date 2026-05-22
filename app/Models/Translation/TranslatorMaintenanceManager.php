<?php

declare(strict_types=1);

namespace App\Models\Translation;

use Nette\Database\Explorer;

final readonly class TranslatorMaintenanceManager
{
    public function __construct(
        private Explorer $database,
    ) {
    }

    public function cleanupTranslationsLog(): ?int
    {
        $sql = <<<MARIADB
            DELETE tl
            FROM translations_log tl
            INNER JOIN translations t
                ON t.`key` = tl.`key`
                AND t.lang = tl.lang
            MARIADB;

        return $this->database
            ->query($sql)
            ->getRowCount();
    }
}
