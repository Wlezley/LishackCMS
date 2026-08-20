<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260820201225 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create basic database structure';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            <<<'SQL'
                CREATE TABLE article (
                    id INT AUTO_INCREMENT NOT NULL,
                    name_url VARCHAR(255) DEFAULT NULL,
                    category_id INT DEFAULT 1,
                    title VARCHAR(255) DEFAULT NULL,
                    content LONGTEXT DEFAULT NULL,
                    published TINYINT DEFAULT NULL,
                    published_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    user_id INT DEFAULT NULL,
                    robots VARCHAR(255) DEFAULT NULL,
                    canonical_url VARCHAR(255) DEFAULT NULL,
                    og_title VARCHAR(255) DEFAULT NULL,
                    og_description VARCHAR(255) DEFAULT NULL,
                    og_image VARCHAR(255) DEFAULT NULL,
                    og_url VARCHAR(255) DEFAULT NULL,
                    og_type VARCHAR(255) DEFAULT NULL,
                    meta_title VARCHAR(255) DEFAULT NULL,
                    meta_description VARCHAR(255) DEFAULT NULL,
                    PRIMARY KEY (id)
                ) DEFAULT CHARACTER SET utf8mb4
                SQL
        );

        $this->addSql(
            <<<'SQL'
                CREATE TABLE category (
                    id INT AUTO_INCREMENT NOT NULL,
                    parent_id INT DEFAULT NULL,
                    position INT DEFAULT NULL,
                    level INT DEFAULT NULL,
                    name VARCHAR(255) DEFAULT NULL,
                    name_url VARCHAR(255) DEFAULT NULL,
                    title VARCHAR(255) DEFAULT NULL,
                    description LONGTEXT DEFAULT NULL,
                    body LONGTEXT DEFAULT NULL,
                    hidden TINYINT DEFAULT 0 NOT NULL,
                    PRIMARY KEY (id)
                ) DEFAULT CHARACTER SET utf8mb4
                SQL
        );

        $this->addSql(
            <<<'SQL'
                CREATE TABLE cms_config (
                    `key` VARCHAR(255) NOT NULL,
                    category VARCHAR(255) NOT NULL,
                    value LONGTEXT DEFAULT NULL,
                    PRIMARY KEY (`key`)
                ) DEFAULT CHARACTER SET utf8mb4
                SQL
        );

        $this->addSql(
            <<<'SQL'
                CREATE TABLE dataset (
                    id INT AUTO_INCREMENT NOT NULL,
                    name VARCHAR(50) NOT NULL,
                    slug VARCHAR(50) NOT NULL,
                    component VARCHAR(50) NOT NULL,
                    presenter VARCHAR(50) NOT NULL,
                    active TINYINT DEFAULT 1 NOT NULL,
                    deleted TINYINT DEFAULT 0 NOT NULL,
                    PRIMARY KEY (id)
                ) DEFAULT CHARACTER SET utf8mb4
                SQL
        );

        $this->addSql(
            <<<'SQL'
                CREATE TABLE dataset_column (
                    id INT AUTO_INCREMENT NOT NULL,
                    dataset_id INT NOT NULL,
                    column_id INT NOT NULL,
                    name VARCHAR(50) NOT NULL,
                    slug VARCHAR(50) NOT NULL,
                    type VARCHAR(50) NOT NULL,
                    required TINYINT DEFAULT 0 NOT NULL,
                    listed TINYINT DEFAULT 0 NOT NULL,
                    hidden TINYINT DEFAULT 0 NOT NULL,
                    deleted TINYINT DEFAULT 0 NOT NULL,
                    `default` LONGTEXT DEFAULT NULL,
                    UNIQUE INDEX UNIQ_1B2553C4D47C2D1B (dataset_id),
                    UNIQUE INDEX UNIQ_1B2553C4BE8E8ED5 (column_id),
                    UNIQUE INDEX UNIQ_1B2553C4989D9B62 (slug),
                    PRIMARY KEY (id)
                ) DEFAULT CHARACTER SET utf8mb4
                SQL
        );

        $this->addSql(
            <<<'SQL'
                CREATE TABLE language (
                    id INT AUTO_INCREMENT NOT NULL,
                    language_code VARCHAR(2) NOT NULL,
                    name VARCHAR(50) NOT NULL,
                    html_lang VARCHAR(2) NOT NULL,
                    locale VARCHAR(5) NOT NULL,
                    enabled TINYINT DEFAULT 1 NOT NULL,
                    `default` TINYINT DEFAULT 0 NOT NULL,
                    UNIQUE INDEX UNIQ_D4DB71B5451CDAD4 (language_code),
                    PRIMARY KEY (id)
                ) DEFAULT CHARACTER SET utf8mb4
                SQL
        );

        $this->addSql(
            <<<'SQL'
                CREATE TABLE log_sms (
                    id INT AUTO_INCREMENT NOT NULL,
                    user_id INT DEFAULT NULL,
                    date DATETIME DEFAULT CURRENT_TIMESTAMP,
                    phone_number VARCHAR(16) NOT NULL,
                    message VARCHAR(460) NOT NULL,
                    error_code SMALLINT DEFAULT NULL,
                    PRIMARY KEY (id)
                ) DEFAULT CHARACTER SET utf8mb4
                SQL
        );

        $this->addSql(
            <<<'SQL'
                CREATE TABLE redirect (
                    id INT AUTO_INCREMENT NOT NULL,
                    source VARCHAR(300) NOT NULL,
                    target VARCHAR(300) NOT NULL,
                    code INT DEFAULT 302 NOT NULL,
                    enabled TINYINT DEFAULT 1 NOT NULL,
                    PRIMARY KEY (id)
                ) DEFAULT CHARACTER SET utf8mb4
                SQL
        );

        $this->addSql(
            <<<'SQL'
                CREATE TABLE storage_files (
                    id INT AUTO_INCREMENT NOT NULL,
                    tree_id INT DEFAULT 0 NOT NULL,
                    owner_id INT DEFAULT 0 NOT NULL,
                    position INT DEFAULT NULL,
                    name VARCHAR(255) NOT NULL,
                    name_url VARCHAR(255) NOT NULL,
                    type VARCHAR(255) NOT NULL,
                    icon VARCHAR(5) NOT NULL,
                    size BIGINT DEFAULT 0 NOT NULL,
                    checksum VARCHAR(32) NOT NULL,
                    storage_id VARCHAR(16) NOT NULL,
                    download_id VARCHAR(16) NOT NULL,
                    uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
                    modified_at DATETIME DEFAULT NULL,
                    deleted_at DATETIME DEFAULT NULL,
                    PRIMARY KEY (id)
                ) DEFAULT CHARACTER SET utf8mb4
                SQL
        );

        $this->addSql(
            <<<'SQL'
                CREATE TABLE storage_tree (
                    id INT AUTO_INCREMENT NOT NULL,
                    parent_id INT DEFAULT 0 NOT NULL,
                    owner_id INT DEFAULT 0 NOT NULL,
                    position INT DEFAULT NULL,
                    name VARCHAR(255) NOT NULL,
                    name_url VARCHAR(255) NOT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
                    modified_at DATETIME DEFAULT NULL,
                    deleted_at DATETIME DEFAULT NULL,
                    PRIMARY KEY (id)
                ) DEFAULT CHARACTER SET utf8mb4
                SQL
        );

        $this->addSql(
            <<<'SQL'
                CREATE TABLE translations (
                    id INT AUTO_INCREMENT NOT NULL,
                    `key` VARCHAR(255) NOT NULL,
                    lang VARCHAR(2) NOT NULL,
                    text LONGTEXT DEFAULT NULL,
                    PRIMARY KEY (id)
                ) DEFAULT CHARACTER SET utf8mb4
                SQL
        );

        $this->addSql(
            <<<'SQL'
                CREATE TABLE translations_log (
                    id INT AUTO_INCREMENT NOT NULL,
                    date DATETIME DEFAULT CURRENT_TIMESTAMP,
                    `key` VARCHAR(255) NOT NULL,
                    lang VARCHAR(2) NOT NULL,
                    type VARCHAR(255) DEFAULT 'unk' NOT NULL,
                    message LONGTEXT NOT NULL,
                    PRIMARY KEY (id)
                ) DEFAULT CHARACTER SET utf8mb4
                SQL
        );

        $this->addSql(
            <<<'SQL'
                CREATE TABLE users (
                    id INT AUTO_INCREMENT NOT NULL,
                    name VARCHAR(50) NOT NULL,
                    password VARCHAR(255) NOT NULL,
                    email VARCHAR(255) NOT NULL,
                    role VARCHAR(50) NOT NULL,
                    full_name VARCHAR(255) NOT NULL,
                    session_id VARCHAR(150) NOT NULL,
                    deleted TINYINT DEFAULT 0 NOT NULL,
                    enabled TINYINT DEFAULT 1 NOT NULL,
                    created DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
                    last_login DATETIME DEFAULT NULL,
                    UNIQUE INDEX UNIQ_1483A5E95E237E06 (name),
                    PRIMARY KEY (id)
                ) DEFAULT CHARACTER SET utf8mb4
                SQL
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE article');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE cms_config');
        $this->addSql('DROP TABLE dataset');
        $this->addSql('DROP TABLE dataset_column');
        $this->addSql('DROP TABLE language');
        $this->addSql('DROP TABLE log_sms');
        $this->addSql('DROP TABLE redirect');
        $this->addSql('DROP TABLE storage_files');
        $this->addSql('DROP TABLE storage_tree');
        $this->addSql('DROP TABLE translations');
        $this->addSql('DROP TABLE translations_log');
        $this->addSql('DROP TABLE users');
    }
}
