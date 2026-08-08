<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class DatasetSystem20250502 extends AbstractMigration
{
    public function up(): void
    {
        $this->execute("
            REPLACE INTO `translations` (`key`, `lang`, `text`) VALUES ('title.admin.data.create', 'cz', 'Vytvoření datové položky');
            REPLACE INTO `translations` (`key`, `lang`, `text`) VALUES ('title.admin.data.create', 'en', 'Create data item');
            REPLACE INTO `translations` (`key`, `lang`, `text`) VALUES ('title.admin.data.default', 'cz', 'Seznam dat');
            REPLACE INTO `translations` (`key`, `lang`, `text`) VALUES ('title.admin.data.default', 'en', 'List of data');
            REPLACE INTO `translations` (`key`, `lang`, `text`) VALUES ('title.admin.data.edit', 'cz', 'Editace datové položky');
            REPLACE INTO `translations` (`key`, `lang`, `text`) VALUES ('title.admin.data.edit', 'en', 'Edit data item');

            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('title.admin.dataset.create', 'cz', 'Vytvoření datasetu');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('title.admin.dataset.create', 'en', 'Create dataset');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('title.admin.dataset.default', 'cz', 'Seznam datasetů');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('title.admin.dataset.default', 'en', 'List of datasets');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('title.admin.dataset.edit', 'cz', 'Editace datasetu');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('title.admin.dataset.edit', 'en', 'Edit dataset');

            DELETE FROM `translations` WHERE `key`='admin.sidebar.main.data.create';
            DELETE FROM `translations` WHERE `key`='admin.sidebar.main.data.list';

            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('admin.sidebar.main.config.dataset', 'cz', 'Datasety');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('admin.sidebar.main.config.dataset', 'en', 'Datasets');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('search.dataset', 'cz', 'Vyhledat dataset');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('search.dataset', 'en', 'Search dataset');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('serach.data', 'cz', 'Vyhledat data');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('serach.data', 'en', 'Search data');
        ");

        $this->execute("
            ALTER TABLE `dataset_column`
                ADD COLUMN `listed` TINYINT(1) NULL DEFAULT '0' AFTER `required`,
                ADD COLUMN `hidden` TINYINT(1) NULL DEFAULT '0' AFTER `listed`,
                ADD COLUMN `default` TEXT NULL DEFAULT NULL AFTER `deleted`;
        ");
    }

    public function down(): void
    {
    }
}
