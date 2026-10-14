<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261014060438 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add category, user and SEO table foreign keys to the article table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE article ADD seo_id INT DEFAULT NULL, DROP published, DROP robots, DROP canonical_url, DROP og_title, DROP og_description, DROP og_image, DROP og_url, DROP og_type, DROP meta_title, DROP meta_description, CHANGE name_url name_url VARCHAR(255) NOT NULL, CHANGE category_id category_id INT DEFAULT NULL, CHANGE title title VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE article ADD CONSTRAINT FK_23A0E6612469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE article ADD CONSTRAINT FK_23A0E66A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE article ADD CONSTRAINT FK_23A0E6697E3DD86 FOREIGN KEY (seo_id) REFERENCES seo (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_23A0E6612469DE2 ON article (category_id)');
        $this->addSql('CREATE INDEX IDX_23A0E66A76ED395 ON article (user_id)');
        $this->addSql('CREATE INDEX IDX_23A0E6697E3DD86 ON article (seo_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE article DROP FOREIGN KEY FK_23A0E6612469DE2');
        $this->addSql('ALTER TABLE article DROP FOREIGN KEY FK_23A0E66A76ED395');
        $this->addSql('ALTER TABLE article DROP FOREIGN KEY FK_23A0E6697E3DD86');
        $this->addSql('DROP INDEX IDX_23A0E6612469DE2 ON article');
        $this->addSql('DROP INDEX IDX_23A0E66A76ED395 ON article');
        $this->addSql('DROP INDEX IDX_23A0E6697E3DD86 ON article');
        $this->addSql('ALTER TABLE article ADD published TINYINT DEFAULT 1 NOT NULL, ADD robots VARCHAR(255) DEFAULT NULL, ADD canonical_url VARCHAR(255) DEFAULT NULL, ADD og_title VARCHAR(255) DEFAULT NULL, ADD og_description VARCHAR(255) DEFAULT NULL, ADD og_image VARCHAR(255) DEFAULT NULL, ADD og_url VARCHAR(255) DEFAULT NULL, ADD og_type VARCHAR(255) DEFAULT NULL, ADD meta_title VARCHAR(255) DEFAULT NULL, ADD meta_description VARCHAR(255) DEFAULT NULL, DROP seo_id, CHANGE title title VARCHAR(255) DEFAULT NULL, CHANGE name_url name_url VARCHAR(255) DEFAULT NULL, CHANGE category_id category_id INT DEFAULT 1');
    }
}
