<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class ArticleEditor extends AbstractMigration
{
    public function up(): void
    {
        $this->execute("
            ALTER TABLE `category`
                ADD COLUMN `level` INT(11) NULL DEFAULT NULL AFTER `position`;
        ");

        $this->execute("
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('form.article.title', 'cz', 'Název stránky');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('form.article.title', 'en', 'Article title');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('form.article.name_url', 'cz', 'Jméno v URL');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('form.article.name_url', 'en', 'Name URL');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('form.article.published', 'cz', 'Publikováno');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('form.article.published', 'en', 'Published');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('form.article.published_at', 'cz', 'Datum publikace');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('form.article.published_at', 'en', 'Date of publication');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('form.article.updated_at', 'cz', 'Datum editace');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('form.article.updated_at', 'en', 'Last update date');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('form.article.robots', 'cz', 'Nastavení indexování');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('form.article.robots', 'en', 'Indexing');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('form.article.canonical_url', 'cz', 'Cannonical URL');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('form.article.canonical_url', 'en', 'Cannonical URL');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('form.article.og_title', 'cz', 'og:title');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('form.article.og_title', 'en', 'og:title');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('form.article.og_description', 'cz', 'og:description');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('form.article.og_description', 'en', 'og:description');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('form.article.og_image', 'cz', 'og:image');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('form.article.og_image', 'en', 'og:image');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('form.article.og_url', 'cz', 'og:url');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('form.article.og_url', 'en', 'og:url');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('form.article.og_type', 'cz', 'og:type');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('form.article.og_type', 'en', 'og:type');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('form.article.meta_title', 'cz', 'meta title');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('form.article.meta_title', 'en', 'meta title');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('form.article.meta_description', 'cz', 'meta description');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('form.article.meta_description', 'en', 'meta description');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('form.article.content', 'cz', 'Obsah článku');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('form.article.content', 'en', 'Article content');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('form.article.save', 'cz', 'Uložit článek');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('form.article.save', 'en', 'Save article');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('form.article.create', 'cz', 'Vytvořit článek');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('form.article.create', 'en', 'Create article');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('form.article.author', 'cz', 'Autor');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('form.article.author', 'en', 'Author');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('author.unknown', 'cz', '(neznámý)');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('author.unknown', 'en', '(unknown)');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('author.anonymous', 'cz', '(anonymní)');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('author.anonymous', 'en', '(anonymous)');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('author.system', 'cz', '(systém)');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('author.system', 'en', '(system)');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('success.form.article-saved', 'cz', 'Článek byl uložen.');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('success.form.article-saved', 'en', 'The article has been saved.');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('success.form.article-created', 'cz', 'Článek byl vytvořen.');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('success.form.article-created', 'en', 'The article has been created.');
        ");
    }

    public function down(): void
    {
        $this->execute("
            ALTER TABLE `category`
                DROP COLUMN `level`;
        ");
    }
}
