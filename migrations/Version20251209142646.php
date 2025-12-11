<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251209142646 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE collaborateur_collaborateur_education (collaborateurId INT NOT NULL, collaborateurEducationId INT NOT NULL, INDEX IDX_797DA995427F5D85 (collaborateurId), INDEX IDX_797DA995D12A42D1 (collaborateurEducationId), PRIMARY KEY(collaborateurId, collaborateurEducationId)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE collaborateureducation (collaborateurEducationId INT AUTO_INCREMENT NOT NULL, collaborateurEducationNatureEtudes VARCHAR(254) DEFAULT NULL, collaborateurEducationEtablissement VARCHAR(254) DEFAULT NULL, collaborateurEducationAnneeObtention INT DEFAULT NULL, typeDiplomeId INT NOT NULL, INDEX IDX_A65EB7CB2DE41727 (typeDiplomeId), PRIMARY KEY(collaborateurEducationId)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sourceappeloffres (sourceAppelOffresId INT AUTO_INCREMENT NOT NULL, sourceAppelOffresLibelle VARCHAR(255) NOT NULL, sourceAppelOffresDescription LONGTEXT DEFAULT NULL, sourceAppelOffresUrl VARCHAR(255) DEFAULT NULL, PRIMARY KEY(sourceAppelOffresId)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE collaborateur_collaborateur_education ADD CONSTRAINT FK_797DA995427F5D85 FOREIGN KEY (collaborateurId) REFERENCES collaborateur (collaborateurId)');
        $this->addSql('ALTER TABLE collaborateur_collaborateur_education ADD CONSTRAINT FK_797DA995D12A42D1 FOREIGN KEY (collaborateurEducationId) REFERENCES collaborateureducation (collaborateurEducationId)');
        $this->addSql('ALTER TABLE collaborateureducation ADD CONSTRAINT FK_A65EB7CB2DE41727 FOREIGN KEY (typeDiplomeId) REFERENCES typediplome (typeDiplomeId)');
        $this->addSql('ALTER TABLE employeeducation DROP FOREIGN KEY FK_Association_35');
        $this->addSql('ALTER TABLE employeeducation DROP FOREIGN KEY FK_BAD1E68C427F5D85');
        $this->addSql('DROP TABLE employeeducation');
        $this->addSql('ALTER TABLE appel_offres ADD sourceAppelOffresId INT DEFAULT NULL, CHANGE appelOffresNumeroDevisParticipation appelOffresNumeroDevisParticipation INT DEFAULT NULL');
        $this->addSql('ALTER TABLE appel_offres ADD CONSTRAINT FK_F30A4DC5C060A87D FOREIGN KEY (sourceAppelOffresId) REFERENCES sourceappeloffres (sourceAppelOffresId)');
        $this->addSql('CREATE INDEX IDX_F30A4DC5C060A87D ON appel_offres (sourceAppelOffresId)');
        $this->addSql('ALTER TABLE reference_collaborateur ADD reference_id INT NOT NULL');
        $this->addSql('ALTER TABLE reference_collaborateur ADD CONSTRAINT FK_54F489AF1645DEA9 FOREIGN KEY (reference_id) REFERENCES reference (referenceID)');
        $this->addSql('CREATE INDEX IDX_54F489AF1645DEA9 ON reference_collaborateur (reference_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE appel_offres DROP FOREIGN KEY FK_F30A4DC5C060A87D');
        $this->addSql('CREATE TABLE employeeducation (employeEducationId INT AUTO_INCREMENT NOT NULL, collaborateurId INT DEFAULT NULL, typeDiplomeId INT DEFAULT NULL, employeEducationNatureEtudes VARCHAR(254) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, employeEducationEtablissement VARCHAR(254) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, employeEducationAnneeObtention DATE NOT NULL, INDEX IDX_BAD1E68C2DE41727 (typeDiplomeId), INDEX IDX_BAD1E68C427F5D85 (collaborateurId), PRIMARY KEY(employeEducationId)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE employeeducation ADD CONSTRAINT FK_Association_35 FOREIGN KEY (typeDiplomeId) REFERENCES typediplome (typeDiplomeId) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE employeeducation ADD CONSTRAINT FK_BAD1E68C427F5D85 FOREIGN KEY (collaborateurId) REFERENCES collaborateur (collaborateurId) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE collaborateur_collaborateur_education DROP FOREIGN KEY FK_797DA995427F5D85');
        $this->addSql('ALTER TABLE collaborateur_collaborateur_education DROP FOREIGN KEY FK_797DA995D12A42D1');
        $this->addSql('ALTER TABLE collaborateureducation DROP FOREIGN KEY FK_A65EB7CB2DE41727');
        $this->addSql('DROP TABLE collaborateur_collaborateur_education');
        $this->addSql('DROP TABLE collaborateureducation');
        $this->addSql('DROP TABLE sourceappeloffres');
        $this->addSql('DROP INDEX IDX_F30A4DC5C060A87D ON appel_offres');
        $this->addSql('ALTER TABLE appel_offres DROP sourceAppelOffresId, CHANGE appelOffresNumeroDevisParticipation appelOffresNumeroDevisParticipation VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE client_pays DROP FOREIGN KEY FK_E503FFCC19EB6921');
        $this->addSql('ALTER TABLE client_pays DROP FOREIGN KEY FK_E503FFCCA6E44244');
        $this->addSql('ALTER TABLE reference_collaborateur DROP FOREIGN KEY FK_54F489AF1645DEA9');
        $this->addSql('DROP INDEX IDX_54F489AF1645DEA9 ON reference_collaborateur');
        $this->addSql('ALTER TABLE reference_collaborateur DROP reference_id');
    }
}
