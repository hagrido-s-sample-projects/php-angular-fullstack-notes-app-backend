<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241021103003 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE token (id SERIAL NOT NULL, session_id INT NOT NULL, token VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, is_revoked BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_5F37A13B613FECDF ON token (session_id)');
        $this->addSql('COMMENT ON COLUMN token.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE token ADD CONSTRAINT FK_5F37A13B613FECDF FOREIGN KEY (session_id) REFERENCES session (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE session DROP access');
        $this->addSql('ALTER TABLE session DROP refresh');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE token DROP CONSTRAINT FK_5F37A13B613FECDF');
        $this->addSql('DROP TABLE token');
        $this->addSql('ALTER TABLE session ADD access JSON NOT NULL');
        $this->addSql('ALTER TABLE session ADD refresh VARCHAR(255) NOT NULL');
    }
}
