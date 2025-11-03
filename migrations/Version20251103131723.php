<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251103131723 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE organisme_demandeur ADD secteur_activite_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE organisme_demandeur ADD CONSTRAINT FK_2F3408545233A7FC FOREIGN KEY (secteur_activite_id) REFERENCES secteur_activite (secteur_activite_id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_2F3408545233A7FC ON organisme_demandeur (secteur_activite_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE organisme_demandeur DROP FOREIGN KEY FK_2F3408545233A7FC');
        $this->addSql('DROP INDEX IDX_2F3408545233A7FC ON organisme_demandeur');
        $this->addSql('ALTER TABLE organisme_demandeur DROP secteur_activite_id');
    }
}
