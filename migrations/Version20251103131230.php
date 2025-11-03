<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251103131230 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE organisme_demandeur ADD nature_organisme_demendeur_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE organisme_demandeur ADD CONSTRAINT FK_2F34085453D9D807 FOREIGN KEY (nature_organisme_demendeur_id) REFERENCES nature_organisme_demendeur (nature_organisme_demendeur_id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_2F34085453D9D807 ON organisme_demandeur (nature_organisme_demendeur_id)');
        $this->addSql('ALTER TABLE pays DROP codeISO');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE organisme_demandeur DROP FOREIGN KEY FK_2F34085453D9D807');
        $this->addSql('DROP INDEX IDX_2F34085453D9D807 ON organisme_demandeur');
        $this->addSql('ALTER TABLE organisme_demandeur DROP nature_organisme_demendeur_id');
        $this->addSql('ALTER TABLE pays ADD codeISO VARCHAR(2) DEFAULT NULL');
    }
}
