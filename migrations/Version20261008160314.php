<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261008160314 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE control_point (id UUID NOT NULL, reference VARCHAR(64) NOT NULL, label VARCHAR(150) NOT NULL, instruction TEXT DEFAULT NULL, frequency VARCHAR(50) NOT NULL, sort_order INT NOT NULL, default_criticality SMALLINT NOT NULL, is_active BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, zone_id UUID NOT NULL, equipment_id UUID DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C5C5A79AEA34913 ON control_point (reference)');
        $this->addSql('CREATE INDEX IDX_C5C5A7945AFA4EA ON control_point (sort_order)');
        $this->addSql('CREATE INDEX IDX_C5C5A799F2C3FAB ON control_point (zone_id)');
        $this->addSql('CREATE INDEX IDX_C5C5A79517FE9FE ON control_point (equipment_id)');
        $this->addSql('CREATE TABLE patrol_result (id UUID NOT NULL, result VARCHAR(255) NOT NULL, comment TEXT DEFAULT NULL, photo_path VARCHAR(255) DEFAULT NULL, checked_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, patrol_round_id UUID NOT NULL, control_point_id UUID NOT NULL, equipment_id UUID DEFAULT NULL, report_id UUID DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_C99B6CDAD77FEDA0 ON patrol_result (checked_at)');
        $this->addSql('CREATE INDEX IDX_C99B6CDA145F3503 ON patrol_result (patrol_round_id)');
        $this->addSql('CREATE INDEX IDX_C99B6CDA1FE83EE2 ON patrol_result (control_point_id)');
        $this->addSql('CREATE INDEX IDX_C99B6CDA517FE9FE ON patrol_result (equipment_id)');
        $this->addSql('CREATE INDEX IDX_C99B6CDA4BD2A4C0 ON patrol_result (report_id)');
        $this->addSql('CREATE TABLE patrol_round (id UUID NOT NULL, reference VARCHAR(32) NOT NULL, type VARCHAR(50) NOT NULL, started_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, finished_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, status VARCHAR(255) NOT NULL, total_points INT NOT NULL, general_observation TEXT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, agent_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_54FBF35FAEA34913 ON patrol_round (reference)');
        $this->addSql('CREATE INDEX IDX_54FBF35FD46F4E3 ON patrol_round (started_at)');
        $this->addSql('CREATE INDEX IDX_54FBF35F7B00651C ON patrol_round (status)');
        $this->addSql('CREATE INDEX IDX_54FBF35F3414710B ON patrol_round (agent_id)');
        $this->addSql('CREATE TABLE priority_threshold (id UUID NOT NULL, name VARCHAR(50) NOT NULL, min_score SMALLINT NOT NULL, max_score SMALLINT NOT NULL, priority VARCHAR(255) NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_895F92C85E237E06 ON priority_threshold (name)');
        $this->addSql('CREATE TABLE report_intervention (id UUID NOT NULL, performed_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, action TEXT NOT NULL, duration_minutes INT DEFAULT NULL, replaced_part VARCHAR(255) DEFAULT NULL, result VARCHAR(100) DEFAULT NULL, attachment_path VARCHAR(255) DEFAULT NULL, report_id UUID NOT NULL, actor_id UUID DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_DF728432CC77A1DC ON report_intervention (performed_at)');
        $this->addSql('CREATE INDEX IDX_DF7284324BD2A4C0 ON report_intervention (report_id)');
        $this->addSql('CREATE INDEX IDX_DF72843210DAF24A ON report_intervention (actor_id)');
        $this->addSql('ALTER TABLE control_point ADD CONSTRAINT FK_C5C5A799F2C3FAB FOREIGN KEY (zone_id) REFERENCES zone (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE control_point ADD CONSTRAINT FK_C5C5A79517FE9FE FOREIGN KEY (equipment_id) REFERENCES equipment (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE patrol_result ADD CONSTRAINT FK_C99B6CDA145F3503 FOREIGN KEY (patrol_round_id) REFERENCES patrol_round (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE patrol_result ADD CONSTRAINT FK_C99B6CDA1FE83EE2 FOREIGN KEY (control_point_id) REFERENCES control_point (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE patrol_result ADD CONSTRAINT FK_C99B6CDA517FE9FE FOREIGN KEY (equipment_id) REFERENCES equipment (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE patrol_result ADD CONSTRAINT FK_C99B6CDA4BD2A4C0 FOREIGN KEY (report_id) REFERENCES report (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE patrol_round ADD CONSTRAINT FK_54FBF35F3414710B FOREIGN KEY (agent_id) REFERENCES app_user (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE report_intervention ADD CONSTRAINT FK_DF7284324BD2A4C0 FOREIGN KEY (report_id) REFERENCES report (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE report_intervention ADD CONSTRAINT FK_DF72843210DAF24A FOREIGN KEY (actor_id) REFERENCES app_user (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE equipment ADD external_url VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE equipment ADD last_synced_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE report ADD service_pilot VARCHAR(150) DEFAULT NULL');
        $this->addSql('ALTER TABLE report ADD support_services JSON NOT NULL DEFAULT \'[]\'');
        $this->addSql('ALTER TABLE report ADD waiting_reason VARCHAR(30) DEFAULT NULL');
        $this->addSql('ALTER TABLE report ADD impact_score SMALLINT NOT NULL DEFAULT 1');
        $this->addSql('ALTER TABLE report ADD urgency_score SMALLINT NOT NULL DEFAULT 1');
        $this->addSql('ALTER TABLE report ADD aggravation_score SMALLINT NOT NULL DEFAULT 1');
        $this->addSql('ALTER TABLE report ADD total_score SMALLINT NOT NULL DEFAULT 3');
        $this->addSql('ALTER TABLE report ADD due_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE zone ADD sort_order INT NOT NULL DEFAULT 0');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE control_point DROP CONSTRAINT FK_C5C5A799F2C3FAB');
        $this->addSql('ALTER TABLE control_point DROP CONSTRAINT FK_C5C5A79517FE9FE');
        $this->addSql('ALTER TABLE patrol_result DROP CONSTRAINT FK_C99B6CDA145F3503');
        $this->addSql('ALTER TABLE patrol_result DROP CONSTRAINT FK_C99B6CDA1FE83EE2');
        $this->addSql('ALTER TABLE patrol_result DROP CONSTRAINT FK_C99B6CDA517FE9FE');
        $this->addSql('ALTER TABLE patrol_result DROP CONSTRAINT FK_C99B6CDA4BD2A4C0');
        $this->addSql('ALTER TABLE patrol_round DROP CONSTRAINT FK_54FBF35F3414710B');
        $this->addSql('ALTER TABLE report_intervention DROP CONSTRAINT FK_DF7284324BD2A4C0');
        $this->addSql('ALTER TABLE report_intervention DROP CONSTRAINT FK_DF72843210DAF24A');
        $this->addSql('DROP TABLE control_point');
        $this->addSql('DROP TABLE patrol_result');
        $this->addSql('DROP TABLE patrol_round');
        $this->addSql('DROP TABLE priority_threshold');
        $this->addSql('DROP TABLE report_intervention');
        $this->addSql('ALTER TABLE equipment DROP external_url');
        $this->addSql('ALTER TABLE equipment DROP last_synced_at');
        $this->addSql('ALTER TABLE report DROP service_pilot');
        $this->addSql('ALTER TABLE report DROP support_services');
        $this->addSql('ALTER TABLE report DROP waiting_reason');
        $this->addSql('ALTER TABLE report DROP impact_score');
        $this->addSql('ALTER TABLE report DROP urgency_score');
        $this->addSql('ALTER TABLE report DROP aggravation_score');
        $this->addSql('ALTER TABLE report DROP total_score');
        $this->addSql('ALTER TABLE report DROP due_at');
        $this->addSql('ALTER TABLE zone DROP sort_order');
    }
}
