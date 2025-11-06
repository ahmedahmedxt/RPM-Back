<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251106085313 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE devises ADD deviseSymbole VARCHAR(3) DEFAULT NULL');
        $this->addSql('ALTER TABLE organisme_demandeur CHANGE organismeDemandeurDescription organismeDemandeurDescription LONGTEXT NOT NULL, CHANGE organismeDemandeurNomCoordinateur organismeDemandeurNomCoordinateur VARCHAR(150) NOT NULL, CHANGE organismeDemandeurEmailCoordinateur organismeDemandeurEmailCoordinateur VARCHAR(180) NOT NULL, CHANGE organismeDemandeurRaisonSocial organismeDemandeurRaisonSocial VARCHAR(255) NOT NULL, CHANGE organismeDemandeurRaisonSocialShort organismeDemandeurRaisonSocialShort VARCHAR(100) NOT NULL, CHANGE organismeDemandeurAdresse organismeDemandeurAdresse VARCHAR(500) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE devises DROP deviseSymbole');
        $this->addSql('ALTER TABLE organisme_demandeur CHANGE organismeDemandeurDescription organismeDemandeurDescription LONGTEXT DEFAULT NULL, CHANGE organismeDemandeurNomCoordinateur organismeDemandeurNomCoordinateur VARCHAR(150) DEFAULT NULL, CHANGE organismeDemandeurEmailCoordinateur organismeDemandeurEmailCoordinateur VARCHAR(180) DEFAULT NULL, CHANGE organismeDemandeurRaisonSocial organismeDemandeurRaisonSocial VARCHAR(255) DEFAULT NULL, CHANGE organismeDemandeurRaisonSocialShort organismeDemandeurRaisonSocialShort VARCHAR(100) DEFAULT NULL, CHANGE organismeDemandeurAdresse organismeDemandeurAdresse VARCHAR(500) DEFAULT NULL');
    }
}
