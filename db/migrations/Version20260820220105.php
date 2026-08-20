<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260820220105 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update users table structure and user fields';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_1483A5E95E237E06 ON users');
        $this->addSql(
            <<<'SQL'
                ALTER TABLE users
                    ADD first_name VARCHAR(255) DEFAULT NULL,
                    ADD last_name VARCHAR(255) DEFAULT NULL,
                    ADD updated_at DATETIME DEFAULT NULL,
                    DROP full_name,
                    CHANGE role role VARCHAR(8) NOT NULL,
                    CHANGE session_id session_id VARCHAR(255) NOT NULL,
                    CHANGE name user_name VARCHAR(50) NOT NULL,
                    CHANGE last_login last_login_at DATETIME DEFAULT NULL,
                    CHANGE created created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL
                SQL
        );
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E924A232CF ON users (user_name)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_1483A5E924A232CF ON users');
        $this->addSql(
            <<<'SQL'
                ALTER TABLE users
                    ADD full_name VARCHAR(255) DEFAULT NULL,
                    ADD last_login DATETIME DEFAULT NULL,
                    DROP first_name,
                    DROP last_name,
                    DROP last_login_at,
                    DROP updated_at,
                    CHANGE role role VARCHAR(50) NOT NULL,
                    CHANGE session_id session_id VARCHAR(150) NOT NULL,
                    CHANGE user_name name VARCHAR(50) NOT NULL,
                    CHANGE created_at created DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL
                SQL
        );
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E95E237E06 ON users (name)');
    }
}
