<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261008000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create the core schema for users, zones, equipment, reports and notifications';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE app_user (id UUID NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, first_name VARCHAR(100) NOT NULL, last_name VARCHAR(100) NOT NULL, is_active BOOLEAN NOT NULL, phone VARCHAR(20) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, last_login_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_APP_USER_EMAIL ON app_user (email)');

        $this->addSql('CREATE TABLE zone (id UUID NOT NULL, code VARCHAR(50) NOT NULL, name VARCHAR(150) NOT NULL, description TEXT DEFAULT NULL, is_active BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_ZONE_CODE ON zone (code)');

        $this->addSql('CREATE TABLE equipment (id UUID NOT NULL, zone_id UUID NOT NULL, code VARCHAR(50) NOT NULL, name VARCHAR(150) NOT NULL, type VARCHAR(100) DEFAULT NULL, description TEXT DEFAULT NULL, is_active BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_EQUIPMENT_CODE ON equipment (code)');
        $this->addSql('CREATE INDEX IDX_EQUIPMENT_ZONE_ID ON equipment (zone_id)');
        $this->addSql('ALTER TABLE equipment ADD CONSTRAINT FK_EQUIPMENT_ZONE_ID FOREIGN KEY (zone_id) REFERENCES zone (id) NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('CREATE TABLE report (id UUID NOT NULL, zone_id UUID NOT NULL, equipment_id UUID DEFAULT NULL, author_id UUID NOT NULL, assigned_to_id UUID DEFAULT NULL, reference VARCHAR(50) NOT NULL, title VARCHAR(150) NOT NULL, description TEXT NOT NULL, priority VARCHAR(255) NOT NULL, status VARCHAR(255) NOT NULL, source VARCHAR(255) NOT NULL, observed_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, resolved_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, closed_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_REPORT_REFERENCE ON report (reference)');
        $this->addSql('CREATE INDEX IDX_REPORT_STATUS ON report (status)');
        $this->addSql('CREATE INDEX IDX_REPORT_PRIORITY ON report (priority)');
        $this->addSql('CREATE INDEX IDX_REPORT_CREATED_AT ON report (created_at)');
        $this->addSql('CREATE INDEX IDX_REPORT_ZONE_ID ON report (zone_id)');
        $this->addSql('CREATE INDEX IDX_REPORT_AUTHOR_ID ON report (author_id)');
        $this->addSql('CREATE INDEX IDX_REPORT_ASSIGNED_TO_ID ON report (assigned_to_id)');
        $this->addSql('ALTER TABLE report ADD CONSTRAINT FK_REPORT_ZONE_ID FOREIGN KEY (zone_id) REFERENCES zone (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE report ADD CONSTRAINT FK_REPORT_EQUIPMENT_ID FOREIGN KEY (equipment_id) REFERENCES equipment (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE report ADD CONSTRAINT FK_REPORT_AUTHOR_ID FOREIGN KEY (author_id) REFERENCES app_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE report ADD CONSTRAINT FK_REPORT_ASSIGNED_TO_ID FOREIGN KEY (assigned_to_id) REFERENCES app_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('CREATE TABLE report_photo (id UUID NOT NULL, report_id UUID NOT NULL, path VARCHAR(255) NOT NULL, original_filename VARCHAR(255) NOT NULL, mime_type VARCHAR(100) NOT NULL, size INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_REPORT_PHOTO_REPORT_ID ON report_photo (report_id)');
        $this->addSql('ALTER TABLE report_photo ADD CONSTRAINT FK_REPORT_PHOTO_REPORT_ID FOREIGN KEY (report_id) REFERENCES report (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE report_photo DROP CONSTRAINT FK_REPORT_PHOTO_REPORT_ID');
        $this->addSql('ALTER TABLE report DROP CONSTRAINT FK_REPORT_ASSIGNED_TO_ID');
        $this->addSql('ALTER TABLE report DROP CONSTRAINT FK_REPORT_AUTHOR_ID');
        $this->addSql('ALTER TABLE report DROP CONSTRAINT FK_REPORT_EQUIPMENT_ID');
        $this->addSql('ALTER TABLE report DROP CONSTRAINT FK_REPORT_ZONE_ID');
        $this->addSql('ALTER TABLE equipment DROP CONSTRAINT FK_EQUIPMENT_ZONE_ID');

        $this->addSql('DROP TABLE IF EXISTS report_photo');
        $this->addSql('DROP TABLE IF EXISTS report');
        $this->addSql('DROP TABLE IF EXISTS equipment');
        $this->addSql('DROP TABLE IF EXISTS zone');
        $this->addSql('DROP TABLE IF EXISTS app_user');
    }
}
