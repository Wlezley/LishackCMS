<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class DatasetSystem20250504 extends AbstractMigration
{
    public function up(): void
    {
        $this->execute(
           "INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('dataset.id.not-set', 'cz', 'ID datasetu nebylo nastaveno.');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('dataset.id.not-set', 'en', 'Dataset ID does not set.');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('dataset.item-id.not-found', 'cz', 'Položka datasetu s ID: %d nebyla nalezena.');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('dataset.item-id.not-found', 'en', 'Dataset item with ID: %d not found.');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('dataset.item-id.not-set', 'cz', 'ID položky datasetu nebylo nastaveno.');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('dataset.item-id.not-set', 'en', 'Dataset item ID does not set.');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('dataset.item.not-created', 'cz', 'Položku datasetu se nepodařilo vytvořit.');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('dataset.item.not-created', 'en', 'Unable to create dataset item.');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('dataset.item.created', 'cz', 'Nová položka datasetu byla vytvořena s ID: %d.');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('dataset.item.created', 'en', 'A new dataset item was created with ID: %d.');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('dataset.item.saved', 'cz', 'Položka datové sady s ID: %d byla uložena.');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('dataset.item.saved', 'en', 'Dataset item with ID: %d has been saved.');"
        );
    }

    public function down(): void
    {
        $this->execute(
           "DELETE FROM `translations` WHERE `key`='dataset.id.not-set';
            DELETE FROM `translations` WHERE `key`='dataset.item-id.not-found';
            DELETE FROM `translations` WHERE `key`='dataset.item-id.not-set';
            DELETE FROM `translations` WHERE `key`='dataset.item.not-created';
            DELETE FROM `translations` WHERE `key`='dataset.item.created';
            DELETE FROM `translations` WHERE `key`='dataset.item.saved';"
        );
    }
}
