<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version5 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE cart ADD applied_discount VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE cart ADD total_value DOUBLE PRECISION NOT NULL DEFAULT 0.0');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE cart DROP applied_discount');
        $this->addSql('ALTER TABLE cart DROP total_value');
    }
}
