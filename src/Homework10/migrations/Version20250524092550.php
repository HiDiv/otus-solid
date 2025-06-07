<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250524092550 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE
                user
                (
                    id BINARY(16) NOT NULL COMMENT '(DC2Type:uuid)',
                    login VARCHAR(180) NOT NULL,
                    roles JSON NOT NULL COMMENT '(DC2Type:json)',
                    password VARCHAR(255) NOT NULL,
                    UNIQUE INDEX UNIQ_IDENTIFIER_LOGIN (login),
                    PRIMARY KEY(id)
                )
            DEFAULT CHARACTER SET
                utf8mb4
            COLLATE
                `utf8mb4_unicode_ci`
            ENGINE = InnoDB
        SQL
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            DROP TABLE user
        SQL
        );
    }
}
