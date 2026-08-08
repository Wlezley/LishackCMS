<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class DatasetSystem20250503 extends AbstractMigration
{
    public function up(): void
    {
        $this->execute(
            "ALTER TABLE `dataset_column`
                DROP INDEX `object_id_column_id`,
                ADD UNIQUE INDEX `dataset_id_column_id` (`dataset_id`, `column_id`) USING BTREE,
                ADD UNIQUE INDEX `dataset_id_slug` (`dataset_id`, `slug`);"
        );

        $this->execute(
           "INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('dataset.column.default', 'cz', 'Výchozí');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('dataset.column.default', 'en', 'Default');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('dataset.column.slug', 'cz', 'Slug');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('dataset.column.slug', 'en', 'Slug');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('dataset.column.required', 'cz', 'Povinný');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('dataset.column.required', 'en', 'Required');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('dataset.column.listed', 'cz', 'V seznamu');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('dataset.column.listed', 'en', 'Listed');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('dataset.column.hidden', 'cz', 'Skrytý');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('dataset.column.hidden', 'en', 'Hidden');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('dataset.column.deleted', 'cz', 'Smazaný');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('dataset.column.deleted', 'en', 'Deleted');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('dataset.column', 'cz', 'Sloupce');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('dataset.column', 'en', 'Columns');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('dataset.metadata', 'cz', 'Metadata datasetu');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('dataset.metadata', 'en', 'Dataset metadata');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('dataset.settings', 'cz', 'Nastavení datasetu');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('dataset.settings', 'en', 'Dataset settings');"
        );
    }

    public function down(): void
    {
        $this->execute(
            "ALTER TABLE `dataset_column`
                DROP INDEX `dataset_id_slug`,
                DROP INDEX `dataset_id_column_id`,
                ADD UNIQUE INDEX `object_id_column_id` (`dataset_id`, `column_id`) USING BTREE;"
        );

        $this->execute(
           "DELETE FROM `translations` WHERE `key`='dataset.column.default';
            DELETE FROM `translations` WHERE `key`='dataset.column.slug';
            DELETE FROM `translations` WHERE `key`='dataset.column.required';
            DELETE FROM `translations` WHERE `key`='dataset.column.listed';
            DELETE FROM `translations` WHERE `key`='dataset.column.hidden';
            DELETE FROM `translations` WHERE `key`='dataset.column.deleted';
            DELETE FROM `translations` WHERE `key`='dataset.column';
            DELETE FROM `translations` WHERE `key`='dataset.metadata';
            DELETE FROM `translations` WHERE `key`='dataset.settings';"
        );
    }
}
