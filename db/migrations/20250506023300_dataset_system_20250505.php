<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class DatasetSystem20250505 extends AbstractMigration
{
    public function up(): void
    {
        $this->execute(
           "INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('dataset.column.type.html', 'cz', 'HTML');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('dataset.column.type.html', 'en', 'HTML');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('modal.body.delete-dataset-data', 'cz', 'Opravdu chcete řádek ID: <strong>%d</strong> smazat?');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('modal.body.delete-dataset-data', 'en', 'Are you sure you want to delete row ID: <strong>%d</strong>?');"
        );
    }

    public function down(): void
    {
        $this->execute(
          "DELETE FROM `translations` WHERE `key`='dataset.column.type.html';
           DELETE FROM `translations` WHERE `key`='modal.body.delete-dataset-data';"
        );
    }
}
