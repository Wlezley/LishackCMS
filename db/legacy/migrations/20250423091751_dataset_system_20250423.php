<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class DatasetSystem20250423 extends AbstractMigration
{
    public function up(): void
    {
        $this->execute("
            ALTER TABLE `dataset`
                CHANGE COLUMN `active` `active` TINYINT(1) NULL DEFAULT '1' AFTER `presenter`,
                ADD COLUMN `deleted` TINYINT(1) NULL DEFAULT '0' AFTER `active`;
        ");

        $this->execute("
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('search.article', 'cz', 'Vyhledat článek');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('search.article', 'en', 'Search article');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('dataset.title', 'cz', 'Název datasetu');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('dataset.title', 'en', 'Dataset name');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('slug', 'cz', 'Slug');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('slug', 'en', 'Slug');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('component', 'cz', 'Komponenta');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('component', 'en', 'Component');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('presenter', 'cz', 'Presenter');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('presenter', 'en', 'Presenter');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('modal.body.delete-dataset', 'cz', 'Opravdu chcete dataset <strong>%s</strong> (ID: %d) smazat?');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('modal.body.delete-dataset', 'en', 'Are you sure you want to delete the dataset <strong>%s</strong> (ID: %d)?');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('dataset.id.not-found', 'cz', 'Dataset ID: %d nebyl nalezen');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('dataset.id.not-found', 'en', 'Dataset ID: %d not found');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('redirect.id.not-found', 'cz', 'Přesměrování ID: %d nebylo nalezeno');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('redirect.id.not-found', 'en', 'Redirection ID: %d not found');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('translation.key.not-found', 'cz', 'Překlad pro \'%s\' nebyl nalezen');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('translation.key.not-found', 'en', 'Translation for \'%s\' does not found');
        ");
    }

    public function down(): void
    {
        $this->execute("
            ALTER TABLE `dataset`
                CHANGE COLUMN `active` `active` TINYINT(1) NULL DEFAULT NULL AFTER `presenter`,
                DROP COLUMN `deleted`;
        ");

        $this->execute("
            DLETE FROM `translations` WHERE `key` = 'search.article';
            DLETE FROM `translations` WHERE `key` = 'dataset.title';
            DLETE FROM `translations` WHERE `key` = 'slug';
            DLETE FROM `translations` WHERE `key` = 'component';
            DLETE FROM `translations` WHERE `key` = 'presenter';
            DLETE FROM `translations` WHERE `key` = 'modal.body.delete-dataset';
            DLETE FROM `translations` WHERE `key` = 'dataset.id.not-found';
            DLETE FROM `translations` WHERE `key` = 'redirect.id.not-found';
            DLETE FROM `translations` WHERE `key` = 'translation.key.not-found';
        ");
    }
}
