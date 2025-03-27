<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CategoryForm extends AbstractMigration
{
    public function up(): void
    {
        $this->execute("
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('form.article.category', 'cz', 'Kategorie');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('form.article.category', 'en', 'Category');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('form.category.name', 'cz', 'Název kategorie');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('form.category.name', 'en', 'Category name');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('form.category.name_url', 'cz', 'Jméno v URL');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('form.category.name_url', 'en', 'Name URL');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('form.category.parent_id', 'cz', 'Nadřazená kategorie');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('form.category.parent_id', 'en', 'Parent category');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('form.category.title', 'cz', 'Titulek kategorie');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('form.category.title', 'en', 'Category title');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('form.category.description', 'cz', 'Popisek kategorie');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('form.category.description', 'en', 'Category description');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('form.category.body', 'cz', 'Anotační text');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('form.category.body', 'en', 'Annotation');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('form.category.hidden', 'cz', 'Kategorie je skrytá');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('form.category.hidden', 'en', 'Category is hidden');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('success.form.category-saved', 'cz', 'Kategorie byla uložena.');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('success.form.category-saved', 'en', 'The category has been saved.');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('success.form.category-created', 'cz', 'Kategorie byla vytvořena.');
            INSERT INTO `translations` (`key`, `lang`, `text`) VALUES ('success.form.category-created', 'en', 'The category has been created.');
        ");
    }

    public function down(): void
    {
    }
}
