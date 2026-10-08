<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261008180500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Drop temporary defaults introduced for backward-compatible schema migration';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE report ALTER support_services DROP DEFAULT');
        $this->addSql('ALTER TABLE report ALTER impact_score DROP DEFAULT');
        $this->addSql('ALTER TABLE report ALTER urgency_score DROP DEFAULT');
        $this->addSql('ALTER TABLE report ALTER aggravation_score DROP DEFAULT');
        $this->addSql('ALTER TABLE report ALTER total_score DROP DEFAULT');
        $this->addSql('ALTER TABLE zone ALTER sort_order DROP DEFAULT');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE report ALTER support_services SET DEFAULT \'[]\'');
        $this->addSql('ALTER TABLE report ALTER impact_score SET DEFAULT 1');
        $this->addSql('ALTER TABLE report ALTER urgency_score SET DEFAULT 1');
        $this->addSql('ALTER TABLE report ALTER aggravation_score SET DEFAULT 1');
        $this->addSql('ALTER TABLE report ALTER total_score SET DEFAULT 3');
        $this->addSql('ALTER TABLE zone ALTER sort_order SET DEFAULT 0');
    }
}

