<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251031151655 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE appel_offres (appelOffresId INT AUTO_INCREMENT NOT NULL, appelOffresObjet VARCHAR(255) DEFAULT NULL, appelOffresDateLimiteRemise DATE DEFAULT NULL, appelOffresHeureLimiteRemise TIME DEFAULT NULL, appelOffresCCRetire INT DEFAULT NULL, appelOffresLienAnnonce VARCHAR(255) DEFAULT NULL, appelOffresCautionBancaire INT DEFAULT NULL, appelOffresTypeParticipationId VARCHAR(20) DEFAULT NULL, appelOffresRemarque LONGTEXT DEFAULT NULL, appelOffresParticipation INT DEFAULT NULL, appelOffresDateParticipation DATE DEFAULT NULL, appelOffresEtat VARCHAR(50) DEFAULT NULL, appelOffresResultatRang INT DEFAULT NULL, appelOffresResultatRangTotal INT DEFAULT NULL, appelOffresNumeroDevisParticipation VARCHAR(50) DEFAULT NULL, appelOffreDateRemise DATE DEFAULT NULL, appelOffreDevis VARCHAR(50) DEFAULT NULL, appelOffreAnnee INT DEFAULT NULL, appelOffresTypeId INT DEFAULT NULL, appelOffresMoyenLivraisonId INT DEFAULT NULL, appelOffresPaysId INT DEFAULT NULL, appelOffresOrganismeDemandeurId INT DEFAULT NULL, appelOffresDevisesId INT DEFAULT NULL, INDEX IDX_F30A4DC5E88A3956 (appelOffresTypeId), INDEX IDX_F30A4DC532DC916C (appelOffresMoyenLivraisonId), INDEX IDX_F30A4DC54628374B (appelOffresPaysId), INDEX IDX_F30A4DC5BD4A9BF3 (appelOffresOrganismeDemandeurId), INDEX IDX_F30A4DC569A1BF6E (appelOffresDevisesId), PRIMARY KEY(appelOffresId)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE appel_offres_partenaires (id INT AUTO_INCREMENT NOT NULL, partenaire_id INT NOT NULL, role VARCHAR(100) DEFAULT NULL, appelOffresId INT NOT NULL, INDEX IDX_E10F36AEFFAF5D99 (appelOffresId), INDEX IDX_E10F36AE98DE13AC (partenaire_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE appel_offres_type (appelOffresTypeId INT AUTO_INCREMENT NOT NULL, appelOffresTypeLibelle VARCHAR(255) NOT NULL, appelOffresTypeShort VARCHAR(50) DEFAULT NULL, UNIQUE INDEX UNIQ_AB76F7D132A601B2 (appelOffresTypeLibelle), PRIMARY KEY(appelOffresTypeId)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE bailleurfond (bailleurFondId INT AUTO_INCREMENT NOT NULL, bailleurFondLibelle VARCHAR(254) DEFAULT NULL, bailleurFondAcronyme VARCHAR(254) DEFAULT NULL, PRIMARY KEY(bailleurFondId)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE categorie (categorieId INT AUTO_INCREMENT NOT NULL, categorieLibelle VARCHAR(255) NOT NULL, categorieShort VARCHAR(255) NOT NULL, categorieCodeRef INT NOT NULL, categorieCodeCouleur VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_497DD634147E737F (categorieLibelle), UNIQUE INDEX UNIQ_497DD63464E548EA (categorieShort), UNIQUE INDEX UNIQ_497DD634DD113742 (categorieCodeRef), UNIQUE INDEX UNIQ_497DD634E1BB45B (categorieCodeCouleur), PRIMARY KEY(categorieId)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE client (clientId INT AUTO_INCREMENT NOT NULL, clientRaisonSocial VARCHAR(254) NOT NULL, clientRaisonSocialShort VARCHAR(254) NOT NULL, clientAdresse VARCHAR(254) NOT NULL, clientTelephone1 VARCHAR(254) NOT NULL, clientTelephone2 VARCHAR(254) NOT NULL, clientTelephone3 VARCHAR(254) NOT NULL, clientEmail VARCHAR(254) NOT NULL, clientPersonneContact1 VARCHAR(254) NOT NULL, clientPersonneContact2 VARCHAR(254) NOT NULL, clientPersonneContact3 VARCHAR(254) NOT NULL, natureClientId INT DEFAULT NULL, INDEX IDX_C74404557FA12140 (natureClientId), PRIMARY KEY(clientId)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE client_pays (client_id INT NOT NULL, pays_id INT NOT NULL, INDEX IDX_E503FFCC19EB6921 (client_id), INDEX IDX_E503FFCCA6E44244 (pays_id), PRIMARY KEY(client_id, pays_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE clientsecteuractivite (clientId INT NOT NULL, secteurActiviteId INT NOT NULL, INDEX IDX_93A254E4EA1CE9BE (clientId), INDEX IDX_93A254E4BEFC80CA (secteurActiviteId), PRIMARY KEY(clientId, secteurActiviteId)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE continent (continentId INT AUTO_INCREMENT NOT NULL, continentName VARCHAR(254) DEFAULT NULL, PRIMARY KEY(continentId)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE cvlangueniveau (cvLangueNiveauId INT AUTO_INCREMENT NOT NULL, cvLangueNiveauLibelle VARCHAR(254) DEFAULT NULL, PRIMARY KEY(cvLangueNiveauId)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE devises (devisesId INT AUTO_INCREMENT NOT NULL, devisesLibelle VARCHAR(254) DEFAULT NULL, devisesAcronyme VARCHAR(10) DEFAULT NULL, PRIMARY KEY(devisesId)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE employe (employeId INT AUTO_INCREMENT NOT NULL, employeNom VARCHAR(254) DEFAULT NULL, employePrenom VARCHAR(254) DEFAULT NULL, employeDateNaissance DATE NOT NULL, employeLieuNaissance VARCHAR(254) NOT NULL, employeAdresse VARCHAR(254) NOT NULL, employePrincipaleQualification VARCHAR(254) NOT NULL, employeFormationAutre VARCHAR(254) NOT NULL, employeAffiliationDesAssociationGroupePro VARCHAR(254) NOT NULL, employeRemarque VARCHAR(254) DEFAULT NULL, situationFamilialeId INT DEFAULT NULL, employePosteId INT DEFAULT NULL, employeLangueId INT DEFAULT NULL, INDEX IDX_F804D3B9A8AEDFE0 (situationFamilialeId), INDEX IDX_F804D3B9AF59822E (employePosteId), INDEX IDX_F804D3B9E6E30F5B (employeLangueId), PRIMARY KEY(employeId)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE employedocuments (employeDocumentsId INT AUTO_INCREMENT NOT NULL, employeDocumentsPdf VARCHAR(254) DEFAULT NULL, employeDocumentsTypeId INT DEFAULT NULL, employeId INT DEFAULT NULL, INDEX IDX_C36BCAD6C91F86B (employeDocumentsTypeId), INDEX IDX_C36BCAD6602D69 (employeId), PRIMARY KEY(employeDocumentsId)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE employeeducation (employeEducationId INT AUTO_INCREMENT NOT NULL, employeEducationNatureEtudes VARCHAR(254) NOT NULL, employeEducationEtablissement VARCHAR(254) NOT NULL, employeEducationAnneeObtention DATE NOT NULL, employeId INT DEFAULT NULL, typeDiplomeId INT DEFAULT NULL, INDEX IDX_BAD1E68C602D69 (employeId), INDEX IDX_BAD1E68C2DE41727 (typeDiplomeId), PRIMARY KEY(employeEducationId)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE employeexperience (employeExperienceId INT AUTO_INCREMENT NOT NULL, employeExperienceOrganismeEmployeur VARCHAR(254) DEFAULT NULL, employeExperiencePeriode VARCHAR(254) DEFAULT NULL, employeExperienceFonctionOccupe VARCHAR(254) DEFAULT NULL, employeId INT NOT NULL, paysId INT DEFAULT NULL, INDEX IDX_89226648602D69 (employeId), INDEX IDX_8922664835569A8D (paysId), PRIMARY KEY(employeExperienceId)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE employelangue (employeLangueId INT AUTO_INCREMENT NOT NULL, employeeLangueLue INT DEFAULT NULL, employeeLangueEcrite INT DEFAULT NULL, employeeLangueParlee INT DEFAULT NULL, employeLangueNiveauId INT DEFAULT NULL, INDEX IDX_547287856101996A (employeLangueNiveauId), PRIMARY KEY(employeLangueId)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE employelangueniveau (employeLangueNiveauId INT AUTO_INCREMENT NOT NULL, employeLangueNiveauLibelle VARCHAR(254) DEFAULT NULL, PRIMARY KEY(employeLangueNiveauId)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE employeposte (employePosteId INT AUTO_INCREMENT NOT NULL, employePosteLibelle VARCHAR(254) DEFAULT NULL, PRIMARY KEY(employePosteId)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE environnementdeveloppement (environnementDeveloppementId INT AUTO_INCREMENT NOT NULL, environnementDeveloppementLibelle VARCHAR(254) DEFAULT NULL, environnementDeveloppementDescription VARCHAR(254) DEFAULT NULL, PRIMARY KEY(environnementDeveloppementId)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE langue (id INT AUTO_INCREMENT NOT NULL, langue_nom VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_9357758EE2452E91 (langue_nom), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE lieu (lieuId INT AUTO_INCREMENT NOT NULL, lieuLibelle VARCHAR(255) NOT NULL, paysId INT DEFAULT NULL, UNIQUE INDEX UNIQ_2F577D5918F8200D (lieuLibelle), INDEX IDX_2F577D5935569A8D (paysId), PRIMARY KEY(lieuId)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE methodologie (methodologieId INT AUTO_INCREMENT NOT NULL, methodologieLibelle VARCHAR(254) DEFAULT NULL, methodologieDescription VARCHAR(254) DEFAULT NULL, PRIMARY KEY(methodologieId)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE moyen_livraison (moyenLivraisonId INT AUTO_INCREMENT NOT NULL, moyenLivraisonLibelle VARCHAR(255) NOT NULL, moyenLivraisonShort VARCHAR(10) NOT NULL, UNIQUE INDEX UNIQ_348289E9EA3748D (moyenLivraisonLibelle), PRIMARY KEY(moyenLivraisonId)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE nationalite (id INT AUTO_INCREMENT NOT NULL, nationalite_libelle VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_9EC4D73F6585FED (nationalite_libelle), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE natureclient (natureClientId INT AUTO_INCREMENT NOT NULL, natureClientLibelle VARCHAR(254) NOT NULL, natureClientDescription VARCHAR(254) DEFAULT NULL, UNIQUE INDEX UNIQ_2F07C3A042B3A93C (natureClientLibelle), PRIMARY KEY(natureClientId)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE notification (id INT AUTO_INCREMENT NOT NULL, appel_offre_id INT DEFAULT NULL, message VARCHAR(255) NOT NULL, date_creation DATETIME NOT NULL, isread TINYINT(1) NOT NULL, INDEX IDX_BF5476CA308E35F8 (appel_offre_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE organisme_demandeur (id INT AUTO_INCREMENT NOT NULL, organismeDemandeurLibelle VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_2F340854A21D60B4 (organismeDemandeurLibelle), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE partenaire (partenaireId INT AUTO_INCREMENT NOT NULL, partenaireLibelle VARCHAR(255) NOT NULL, partenaireAcronyme VARCHAR(50) DEFAULT NULL, partenairePremierResponsable VARCHAR(255) DEFAULT NULL, partenairePremierResponsableEmail VARCHAR(255) DEFAULT NULL, partenairePremierResponsableTelephone VARCHAR(50) DEFAULT NULL, partenairePremierResponsableAdresse LONGTEXT DEFAULT NULL, partenairePays VARCHAR(100) DEFAULT NULL, partenaireEmail VARCHAR(255) DEFAULT NULL, partenaireTelephone1 VARCHAR(50) DEFAULT NULL, partenaireTelephone2 VARCHAR(50) DEFAULT NULL, partenaireSiteWeb VARCHAR(255) DEFAULT NULL, partenaireLinkedIn VARCHAR(255) DEFAULT NULL, PRIMARY KEY(partenaireId)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pays (paysId INT AUTO_INCREMENT NOT NULL, paysLibelle VARCHAR(254) NOT NULL, paysCapitale VARCHAR(254) NOT NULL, continentId INT DEFAULT NULL, UNIQUE INDEX UNIQ_349F3CAE23588E17 (paysLibelle), UNIQUE INDEX UNIQ_349F3CAE41BCE223 (paysCapitale), INDEX IDX_349F3CAEA08155E4 (continentId), PRIMARY KEY(paysId)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE poste (id INT AUTO_INCREMENT NOT NULL, poste_nom VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_7C890FAB3C18B63 (poste_nom), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE project_templates (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, description LONGTEXT DEFAULT NULL, hidden_fields JSON NOT NULL, is_system TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE projet (id INT AUTO_INCREMENT NOT NULL, lieu_id INT DEFAULT NULL, client_id INT DEFAULT NULL, projet_libelle VARCHAR(255) NOT NULL, projet_description LONGTEXT NOT NULL, projet_reference VARCHAR(255) NOT NULL, projet_date_demarrage DATE NOT NULL, projet_date_achevement DATE NOT NULL, projet_url_fonctionnel VARCHAR(255) NOT NULL, projet_description_service_effectivement_rendus LONGTEXT NOT NULL, INDEX IDX_50159CA96AB213CC (lieu_id), INDEX IDX_50159CA919EB6921 (client_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE projet_categorie (projet_id INT NOT NULL, categorie_id INT NOT NULL, INDEX IDX_6A8331E0C18272 (projet_id), INDEX IDX_6A8331E0BCF5E72D (categorie_id), PRIMARY KEY(projet_id, categorie_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE projet_employe_poste (id INT AUTO_INCREMENT NOT NULL, duree VARCHAR(255) NOT NULL, employeId INT DEFAULT NULL, INDEX IDX_1C574496602D69 (employeId), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE projet_preuve (id INT AUTO_INCREMENT NOT NULL, projet_preuve_libelle VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reference (referenceID INT AUTO_INCREMENT NOT NULL, referenceRef VARCHAR(254) DEFAULT NULL, referenceTitre VARCHAR(254) DEFAULT NULL, referenceLibelle VARCHAR(254) DEFAULT NULL, referenceUrlFonctionnel VARCHAR(254) DEFAULT NULL, referenceDuree INT DEFAULT NULL, referenceDateDemarrage DATETIME DEFAULT NULL, referenceDateAchevement DATETIME DEFAULT NULL, referenceAnneeAchevement INT DEFAULT NULL, referenceDateReceptionProvisoire DATETIME DEFAULT NULL, referenceDateReceptionDefinitive DATETIME DEFAULT NULL, referenceCaracteristiques VARCHAR(1000) DEFAULT NULL, referenceDescription VARCHAR(1000) DEFAULT NULL, referenceDescriptionServiceEffectivemenetRendus VARCHAR(1000) DEFAULT NULL, referenceDureeGarantie INT DEFAULT NULL, referenceBudget DOUBLE PRECISION DEFAULT NULL, referencePartBudgetGroupement VARCHAR(100) DEFAULT NULL, referenceRemarque VARCHAR(1000) DEFAULT NULL, clientId INT NOT NULL, devisesId INT NOT NULL, lieuId INT NOT NULL, categorieId INT NOT NULL, INDEX IDX_AEA34913EA1CE9BE (clientId), INDEX IDX_AEA349135097CB70 (devisesId), INDEX IDX_AEA349136F708739 (lieuId), INDEX IDX_AEA34913FE278E99 (categorieId), PRIMARY KEY(referenceID)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE referencebailleurfond (referenceID INT NOT NULL, bailleurFondId INT NOT NULL, INDEX IDX_6B99EE017F3C753F (referenceID), INDEX IDX_6B99EE01FF10A631 (bailleurFondId), PRIMARY KEY(referenceID, bailleurFondId)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE referencerole (referenceID INT NOT NULL, roleId INT NOT NULL, INDEX IDX_DECAA5917F3C753F (referenceID), INDEX IDX_DECAA591B8C2FD88 (roleId), PRIMARY KEY(referenceID, roleId)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE referencetechnologie (referenceID INT NOT NULL, technologieId INT NOT NULL, INDEX IDX_9AD4F3437F3C753F (referenceID), INDEX IDX_9AD4F343AE54C718 (technologieId), PRIMARY KEY(referenceID, technologieId)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE referencemethodologie (referenceID INT NOT NULL, methodologieId INT NOT NULL, INDEX IDX_3850E58C7F3C753F (referenceID), INDEX IDX_3850E58CE143CF40 (methodologieId), PRIMARY KEY(referenceID, methodologieId)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE referenceenvironnementdeveloppement (referenceID INT NOT NULL, environnementDeveloppementId INT NOT NULL, INDEX IDX_A4C027A47F3C753F (referenceID), INDEX IDX_A4C027A4AFA6B033 (environnementDeveloppementId), PRIMARY KEY(referenceID, environnementDeveloppementId)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reference_employe (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE referencedocuments (referenceDocumentsId INT AUTO_INCREMENT NOT NULL, referenceDocumentsLibelle VARCHAR(255) DEFAULT NULL, typeDocumentId INT DEFAULT NULL, referenceID INT DEFAULT NULL, INDEX IDX_547A6C97E8B2CC08 (typeDocumentId), INDEX IDX_547A6C977F3C753F (referenceID), PRIMARY KEY(referenceDocumentsId)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE role (roleId INT AUTO_INCREMENT NOT NULL, roleLibelle VARCHAR(255) DEFAULT NULL, roleShort VARCHAR(255) DEFAULT NULL, PRIMARY KEY(roleId)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE secteuractivite (secteurActiviteId INT AUTO_INCREMENT NOT NULL, secteurActiviteLibelle VARCHAR(254) DEFAULT NULL, secteurActiviteDescription VARCHAR(254) DEFAULT NULL, PRIMARY KEY(secteurActiviteId)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE situationfamiliale (situationFamilialeId INT AUTO_INCREMENT NOT NULL, situationFamilialeLibelle VARCHAR(254) NOT NULL, UNIQUE INDEX UNIQ_EB87AFF6910843DB (situationFamilialeLibelle), PRIMARY KEY(situationFamilialeId)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE technologie (technologieId INT AUTO_INCREMENT NOT NULL, referenceTechnologieLibelle VARCHAR(254) DEFAULT NULL, referenceTechnologieDescription TEXT DEFAULT NULL, PRIMARY KEY(technologieId)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE typediplome (typeDiplomeId INT AUTO_INCREMENT NOT NULL, typeDiplomeLibelle VARCHAR(254) DEFAULT NULL, PRIMARY KEY(typeDiplomeId)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE typedocument (typeDocumentId INT AUTO_INCREMENT NOT NULL, typeDocumentLibelle VARCHAR(255) DEFAULT NULL, PRIMARY KEY(typeDocumentId)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE upload_file (id INT AUTO_INCREMENT NOT NULL, projet_preuve_id INT DEFAULT NULL, file_name VARCHAR(255) NOT NULL, file_path VARCHAR(255) NOT NULL, INDEX IDX_81BB169BA2A570E (projet_preuve_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, username VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, reset_token VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE appel_offres ADD CONSTRAINT FK_F30A4DC5E88A3956 FOREIGN KEY (appelOffresTypeId) REFERENCES appel_offres_type (appelOffresTypeId)');
        $this->addSql('ALTER TABLE appel_offres ADD CONSTRAINT FK_F30A4DC532DC916C FOREIGN KEY (appelOffresMoyenLivraisonId) REFERENCES moyen_livraison (moyenLivraisonId)');
        $this->addSql('ALTER TABLE appel_offres ADD CONSTRAINT FK_F30A4DC54628374B FOREIGN KEY (appelOffresPaysId) REFERENCES pays (paysId)');
        $this->addSql('ALTER TABLE appel_offres ADD CONSTRAINT FK_F30A4DC5BD4A9BF3 FOREIGN KEY (appelOffresOrganismeDemandeurId) REFERENCES organisme_demandeur (id)');
        $this->addSql('ALTER TABLE appel_offres ADD CONSTRAINT FK_F30A4DC569A1BF6E FOREIGN KEY (appelOffresDevisesId) REFERENCES devises (devisesId)');
        $this->addSql('ALTER TABLE appel_offres_partenaires ADD CONSTRAINT FK_E10F36AEFFAF5D99 FOREIGN KEY (appelOffresId) REFERENCES appel_offres (appelOffresId) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE appel_offres_partenaires ADD CONSTRAINT FK_E10F36AE98DE13AC FOREIGN KEY (partenaire_id) REFERENCES partenaire (partenaireId) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE client ADD CONSTRAINT FK_C74404557FA12140 FOREIGN KEY (natureClientId) REFERENCES natureclient (natureClientId)');
        $this->addSql('ALTER TABLE client_pays ADD CONSTRAINT FK_E503FFCC19EB6921 FOREIGN KEY (client_id) REFERENCES client (clientId)');
        $this->addSql('ALTER TABLE client_pays ADD CONSTRAINT FK_E503FFCCA6E44244 FOREIGN KEY (pays_id) REFERENCES pays (paysId)');
        $this->addSql('ALTER TABLE clientsecteuractivite ADD CONSTRAINT FK_93A254E4EA1CE9BE FOREIGN KEY (clientId) REFERENCES client (clientId)');
        $this->addSql('ALTER TABLE clientsecteuractivite ADD CONSTRAINT FK_93A254E4BEFC80CA FOREIGN KEY (secteurActiviteId) REFERENCES secteuractivite (secteurActiviteId)');
        $this->addSql('ALTER TABLE employe ADD CONSTRAINT FK_F804D3B9A8AEDFE0 FOREIGN KEY (situationFamilialeId) REFERENCES situationfamiliale (situationFamilialeId)');
        $this->addSql('ALTER TABLE employe ADD CONSTRAINT FK_F804D3B9AF59822E FOREIGN KEY (employePosteId) REFERENCES employeposte (employePosteId)');
        $this->addSql('ALTER TABLE employe ADD CONSTRAINT FK_F804D3B9E6E30F5B FOREIGN KEY (employeLangueId) REFERENCES employelangue (employeLangueId)');
        $this->addSql('ALTER TABLE employedocuments ADD CONSTRAINT FK_C36BCAD6C91F86B FOREIGN KEY (employeDocumentsTypeId) REFERENCES typedocument (typeDocumentId)');
        $this->addSql('ALTER TABLE employedocuments ADD CONSTRAINT FK_C36BCAD6602D69 FOREIGN KEY (employeId) REFERENCES employe (employeId)');
        $this->addSql('ALTER TABLE employeeducation ADD CONSTRAINT FK_BAD1E68C602D69 FOREIGN KEY (employeId) REFERENCES employe (employeId)');
        $this->addSql('ALTER TABLE employeeducation ADD CONSTRAINT FK_BAD1E68C2DE41727 FOREIGN KEY (typeDiplomeId) REFERENCES typediplome (typeDiplomeId)');
        $this->addSql('ALTER TABLE employeexperience ADD CONSTRAINT FK_89226648602D69 FOREIGN KEY (employeId) REFERENCES employe (employeId)');
        $this->addSql('ALTER TABLE employeexperience ADD CONSTRAINT FK_8922664835569A8D FOREIGN KEY (paysId) REFERENCES pays (paysId)');
        $this->addSql('ALTER TABLE employelangue ADD CONSTRAINT FK_547287856101996A FOREIGN KEY (employeLangueNiveauId) REFERENCES employelangueniveau (employeLangueNiveauId)');
        $this->addSql('ALTER TABLE lieu ADD CONSTRAINT FK_2F577D5935569A8D FOREIGN KEY (paysId) REFERENCES pays (paysId)');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA308E35F8 FOREIGN KEY (appel_offre_id) REFERENCES appel_offres (appelOffresId)');
        $this->addSql('ALTER TABLE pays ADD CONSTRAINT FK_349F3CAEA08155E4 FOREIGN KEY (continentId) REFERENCES continent (continentId)');
        $this->addSql('ALTER TABLE projet ADD CONSTRAINT FK_50159CA96AB213CC FOREIGN KEY (lieu_id) REFERENCES lieu (lieuId)');
        $this->addSql('ALTER TABLE projet ADD CONSTRAINT FK_50159CA919EB6921 FOREIGN KEY (client_id) REFERENCES client (clientId)');
        $this->addSql('ALTER TABLE projet_categorie ADD CONSTRAINT FK_6A8331E0C18272 FOREIGN KEY (projet_id) REFERENCES projet (id)');
        $this->addSql('ALTER TABLE projet_categorie ADD CONSTRAINT FK_6A8331E0BCF5E72D FOREIGN KEY (categorie_id) REFERENCES categorie (categorieId)');
        $this->addSql('ALTER TABLE projet_employe_poste ADD CONSTRAINT FK_1C574496602D69 FOREIGN KEY (employeId) REFERENCES employe (employeId)');
        $this->addSql('ALTER TABLE reference ADD CONSTRAINT FK_AEA34913EA1CE9BE FOREIGN KEY (clientId) REFERENCES client (clientId)');
        $this->addSql('ALTER TABLE reference ADD CONSTRAINT FK_AEA349135097CB70 FOREIGN KEY (devisesId) REFERENCES devises (devisesId)');
        $this->addSql('ALTER TABLE reference ADD CONSTRAINT FK_AEA349136F708739 FOREIGN KEY (lieuId) REFERENCES lieu (lieuId)');
        $this->addSql('ALTER TABLE reference ADD CONSTRAINT FK_AEA34913FE278E99 FOREIGN KEY (categorieId) REFERENCES categorie (categorieId)');
        $this->addSql('ALTER TABLE referencebailleurfond ADD CONSTRAINT FK_6B99EE017F3C753F FOREIGN KEY (referenceID) REFERENCES reference (referenceID)');
        $this->addSql('ALTER TABLE referencebailleurfond ADD CONSTRAINT FK_6B99EE01FF10A631 FOREIGN KEY (bailleurFondId) REFERENCES bailleurfond (bailleurFondId)');
        $this->addSql('ALTER TABLE referencerole ADD CONSTRAINT FK_DECAA5917F3C753F FOREIGN KEY (referenceID) REFERENCES reference (referenceID)');
        $this->addSql('ALTER TABLE referencerole ADD CONSTRAINT FK_DECAA591B8C2FD88 FOREIGN KEY (roleId) REFERENCES role (roleId)');
        $this->addSql('ALTER TABLE referencetechnologie ADD CONSTRAINT FK_9AD4F3437F3C753F FOREIGN KEY (referenceID) REFERENCES reference (referenceID)');
        $this->addSql('ALTER TABLE referencetechnologie ADD CONSTRAINT FK_9AD4F343AE54C718 FOREIGN KEY (technologieId) REFERENCES technologie (technologieId)');
        $this->addSql('ALTER TABLE referencemethodologie ADD CONSTRAINT FK_3850E58C7F3C753F FOREIGN KEY (referenceID) REFERENCES reference (referenceID)');
        $this->addSql('ALTER TABLE referencemethodologie ADD CONSTRAINT FK_3850E58CE143CF40 FOREIGN KEY (methodologieId) REFERENCES methodologie (methodologieId)');
        $this->addSql('ALTER TABLE referenceenvironnementdeveloppement ADD CONSTRAINT FK_A4C027A47F3C753F FOREIGN KEY (referenceID) REFERENCES reference (referenceID)');
        $this->addSql('ALTER TABLE referenceenvironnementdeveloppement ADD CONSTRAINT FK_A4C027A4AFA6B033 FOREIGN KEY (environnementDeveloppementId) REFERENCES environnementdeveloppement (environnementDeveloppementId)');
        $this->addSql('ALTER TABLE referencedocuments ADD CONSTRAINT FK_547A6C97E8B2CC08 FOREIGN KEY (typeDocumentId) REFERENCES typedocument (typeDocumentId)');
        $this->addSql('ALTER TABLE referencedocuments ADD CONSTRAINT FK_547A6C977F3C753F FOREIGN KEY (referenceID) REFERENCES reference (referenceID)');
        $this->addSql('ALTER TABLE upload_file ADD CONSTRAINT FK_81BB169BA2A570E FOREIGN KEY (projet_preuve_id) REFERENCES projet_preuve (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE appel_offres DROP FOREIGN KEY FK_F30A4DC5E88A3956');
        $this->addSql('ALTER TABLE appel_offres DROP FOREIGN KEY FK_F30A4DC532DC916C');
        $this->addSql('ALTER TABLE appel_offres DROP FOREIGN KEY FK_F30A4DC54628374B');
        $this->addSql('ALTER TABLE appel_offres DROP FOREIGN KEY FK_F30A4DC5BD4A9BF3');
        $this->addSql('ALTER TABLE appel_offres DROP FOREIGN KEY FK_F30A4DC569A1BF6E');
        $this->addSql('ALTER TABLE appel_offres_partenaires DROP FOREIGN KEY FK_E10F36AEFFAF5D99');
        $this->addSql('ALTER TABLE appel_offres_partenaires DROP FOREIGN KEY FK_E10F36AE98DE13AC');
        $this->addSql('ALTER TABLE client DROP FOREIGN KEY FK_C74404557FA12140');
        $this->addSql('ALTER TABLE client_pays DROP FOREIGN KEY FK_E503FFCC19EB6921');
        $this->addSql('ALTER TABLE client_pays DROP FOREIGN KEY FK_E503FFCCA6E44244');
        $this->addSql('ALTER TABLE clientsecteuractivite DROP FOREIGN KEY FK_93A254E4EA1CE9BE');
        $this->addSql('ALTER TABLE clientsecteuractivite DROP FOREIGN KEY FK_93A254E4BEFC80CA');
        $this->addSql('ALTER TABLE employe DROP FOREIGN KEY FK_F804D3B9A8AEDFE0');
        $this->addSql('ALTER TABLE employe DROP FOREIGN KEY FK_F804D3B9AF59822E');
        $this->addSql('ALTER TABLE employe DROP FOREIGN KEY FK_F804D3B9E6E30F5B');
        $this->addSql('ALTER TABLE employedocuments DROP FOREIGN KEY FK_C36BCAD6C91F86B');
        $this->addSql('ALTER TABLE employedocuments DROP FOREIGN KEY FK_C36BCAD6602D69');
        $this->addSql('ALTER TABLE employeeducation DROP FOREIGN KEY FK_BAD1E68C602D69');
        $this->addSql('ALTER TABLE employeeducation DROP FOREIGN KEY FK_BAD1E68C2DE41727');
        $this->addSql('ALTER TABLE employeexperience DROP FOREIGN KEY FK_89226648602D69');
        $this->addSql('ALTER TABLE employeexperience DROP FOREIGN KEY FK_8922664835569A8D');
        $this->addSql('ALTER TABLE employelangue DROP FOREIGN KEY FK_547287856101996A');
        $this->addSql('ALTER TABLE lieu DROP FOREIGN KEY FK_2F577D5935569A8D');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CA308E35F8');
        $this->addSql('ALTER TABLE pays DROP FOREIGN KEY FK_349F3CAEA08155E4');
        $this->addSql('ALTER TABLE projet DROP FOREIGN KEY FK_50159CA96AB213CC');
        $this->addSql('ALTER TABLE projet DROP FOREIGN KEY FK_50159CA919EB6921');
        $this->addSql('ALTER TABLE projet_categorie DROP FOREIGN KEY FK_6A8331E0C18272');
        $this->addSql('ALTER TABLE projet_categorie DROP FOREIGN KEY FK_6A8331E0BCF5E72D');
        $this->addSql('ALTER TABLE projet_employe_poste DROP FOREIGN KEY FK_1C574496602D69');
        $this->addSql('ALTER TABLE reference DROP FOREIGN KEY FK_AEA34913EA1CE9BE');
        $this->addSql('ALTER TABLE reference DROP FOREIGN KEY FK_AEA349135097CB70');
        $this->addSql('ALTER TABLE reference DROP FOREIGN KEY FK_AEA349136F708739');
        $this->addSql('ALTER TABLE reference DROP FOREIGN KEY FK_AEA34913FE278E99');
        $this->addSql('ALTER TABLE referencebailleurfond DROP FOREIGN KEY FK_6B99EE017F3C753F');
        $this->addSql('ALTER TABLE referencebailleurfond DROP FOREIGN KEY FK_6B99EE01FF10A631');
        $this->addSql('ALTER TABLE referencerole DROP FOREIGN KEY FK_DECAA5917F3C753F');
        $this->addSql('ALTER TABLE referencerole DROP FOREIGN KEY FK_DECAA591B8C2FD88');
        $this->addSql('ALTER TABLE referencetechnologie DROP FOREIGN KEY FK_9AD4F3437F3C753F');
        $this->addSql('ALTER TABLE referencetechnologie DROP FOREIGN KEY FK_9AD4F343AE54C718');
        $this->addSql('ALTER TABLE referencemethodologie DROP FOREIGN KEY FK_3850E58C7F3C753F');
        $this->addSql('ALTER TABLE referencemethodologie DROP FOREIGN KEY FK_3850E58CE143CF40');
        $this->addSql('ALTER TABLE referenceenvironnementdeveloppement DROP FOREIGN KEY FK_A4C027A47F3C753F');
        $this->addSql('ALTER TABLE referenceenvironnementdeveloppement DROP FOREIGN KEY FK_A4C027A4AFA6B033');
        $this->addSql('ALTER TABLE referencedocuments DROP FOREIGN KEY FK_547A6C97E8B2CC08');
        $this->addSql('ALTER TABLE referencedocuments DROP FOREIGN KEY FK_547A6C977F3C753F');
        $this->addSql('ALTER TABLE upload_file DROP FOREIGN KEY FK_81BB169BA2A570E');
        $this->addSql('DROP TABLE appel_offres');
        $this->addSql('DROP TABLE appel_offres_partenaires');
        $this->addSql('DROP TABLE appel_offres_type');
        $this->addSql('DROP TABLE bailleurfond');
        $this->addSql('DROP TABLE categorie');
        $this->addSql('DROP TABLE client');
        $this->addSql('DROP TABLE client_pays');
        $this->addSql('DROP TABLE clientsecteuractivite');
        $this->addSql('DROP TABLE continent');
        $this->addSql('DROP TABLE cvlangueniveau');
        $this->addSql('DROP TABLE devises');
        $this->addSql('DROP TABLE employe');
        $this->addSql('DROP TABLE employedocuments');
        $this->addSql('DROP TABLE employeeducation');
        $this->addSql('DROP TABLE employeexperience');
        $this->addSql('DROP TABLE employelangue');
        $this->addSql('DROP TABLE employelangueniveau');
        $this->addSql('DROP TABLE employeposte');
        $this->addSql('DROP TABLE environnementdeveloppement');
        $this->addSql('DROP TABLE langue');
        $this->addSql('DROP TABLE lieu');
        $this->addSql('DROP TABLE methodologie');
        $this->addSql('DROP TABLE moyen_livraison');
        $this->addSql('DROP TABLE nationalite');
        $this->addSql('DROP TABLE natureclient');
        $this->addSql('DROP TABLE notification');
        $this->addSql('DROP TABLE organisme_demandeur');
        $this->addSql('DROP TABLE partenaire');
        $this->addSql('DROP TABLE pays');
        $this->addSql('DROP TABLE poste');
        $this->addSql('DROP TABLE project_templates');
        $this->addSql('DROP TABLE projet');
        $this->addSql('DROP TABLE projet_categorie');
        $this->addSql('DROP TABLE projet_employe_poste');
        $this->addSql('DROP TABLE projet_preuve');
        $this->addSql('DROP TABLE reference');
        $this->addSql('DROP TABLE referencebailleurfond');
        $this->addSql('DROP TABLE referencerole');
        $this->addSql('DROP TABLE referencetechnologie');
        $this->addSql('DROP TABLE referencemethodologie');
        $this->addSql('DROP TABLE referenceenvironnementdeveloppement');
        $this->addSql('DROP TABLE reference_employe');
        $this->addSql('DROP TABLE referencedocuments');
        $this->addSql('DROP TABLE role');
        $this->addSql('DROP TABLE secteuractivite');
        $this->addSql('DROP TABLE situationfamiliale');
        $this->addSql('DROP TABLE technologie');
        $this->addSql('DROP TABLE typediplome');
        $this->addSql('DROP TABLE typedocument');
        $this->addSql('DROP TABLE upload_file');
        $this->addSql('DROP TABLE `user`');
    }
}
