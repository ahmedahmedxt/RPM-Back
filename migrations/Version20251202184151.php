<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251202184151 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE continent DROP continentAcronymes');
        $this->addSql('ALTER TABLE pays DROP codeISO');
        $this->addSql('ALTER TABLE reference DROP FOREIGN KEY FK_AEA34913658D0DB4');
        $this->addSql('ALTER TABLE reference DROP FOREIGN KEY FK_AEA349136AB213CC');
        $this->addSql('ALTER TABLE reference DROP FOREIGN KEY FK_AEA349137395634A');
        $this->addSql('ALTER TABLE reference DROP FOREIGN KEY FK_AEA34913A848E3B1');
        $this->addSql('DROP INDEX IDX_AEA349136AB213CC ON reference');
        $this->addSql('DROP INDEX IDX_AEA34913658D0DB4 ON reference');
        $this->addSql('DROP INDEX IDX_AEA349137395634A ON reference');
        $this->addSql('DROP INDEX IDX_AEA34913A848E3B1 ON reference');
        $this->addSql('ALTER TABLE reference ADD paysId INT DEFAULT NULL, ADD lieuId INT DEFAULT NULL, ADD devisesId INT DEFAULT NULL, ADD categorieId INT DEFAULT NULL, ADD collaborateurId INT DEFAULT NULL, DROP lieu_id, DROP devises_id, DROP categorie_service_id, DROP collaborateur_id');
        $this->addSql('ALTER TABLE reference ADD CONSTRAINT FK_AEA3491335569A8D FOREIGN KEY (paysId) REFERENCES pays (paysId)');
        $this->addSql('ALTER TABLE reference ADD CONSTRAINT FK_AEA349136F708739 FOREIGN KEY (lieuId) REFERENCES lieu (lieuId)');
        $this->addSql('ALTER TABLE reference ADD CONSTRAINT FK_AEA349135097CB70 FOREIGN KEY (devisesId) REFERENCES devises (devisesId)');
        $this->addSql('ALTER TABLE reference ADD CONSTRAINT FK_AEA34913FE278E99 FOREIGN KEY (categorieId) REFERENCES categorie (categorieId)');
        $this->addSql('ALTER TABLE reference ADD CONSTRAINT FK_AEA34913427F5D85 FOREIGN KEY (collaborateurId) REFERENCES collaborateur (collaborateurId)');
        $this->addSql('CREATE INDEX IDX_AEA3491335569A8D ON reference (paysId)');
        $this->addSql('CREATE INDEX IDX_AEA349136F708739 ON reference (lieuId)');
        $this->addSql('CREATE INDEX IDX_AEA349135097CB70 ON reference (devisesId)');
        $this->addSql('CREATE INDEX IDX_AEA34913FE278E99 ON reference (categorieId)');
        $this->addSql('CREATE INDEX IDX_AEA34913427F5D85 ON reference (collaborateurId)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE continent ADD continentAcronymes VARCHAR(2) NOT NULL');
        $this->addSql('ALTER TABLE pays ADD codeISO VARCHAR(2) NOT NULL');
        $this->addSql('ALTER TABLE reference DROP FOREIGN KEY FK_AEA3491335569A8D');
        $this->addSql('ALTER TABLE reference DROP FOREIGN KEY FK_AEA349136F708739');
        $this->addSql('ALTER TABLE reference DROP FOREIGN KEY FK_AEA349135097CB70');
        $this->addSql('ALTER TABLE reference DROP FOREIGN KEY FK_AEA34913FE278E99');
        $this->addSql('ALTER TABLE reference DROP FOREIGN KEY FK_AEA34913427F5D85');
        $this->addSql('DROP INDEX IDX_AEA3491335569A8D ON reference');
        $this->addSql('DROP INDEX IDX_AEA349136F708739 ON reference');
        $this->addSql('DROP INDEX IDX_AEA349135097CB70 ON reference');
        $this->addSql('DROP INDEX IDX_AEA34913FE278E99 ON reference');
        $this->addSql('DROP INDEX IDX_AEA34913427F5D85 ON reference');
        $this->addSql('ALTER TABLE reference ADD lieu_id INT DEFAULT NULL, ADD devises_id INT DEFAULT NULL, ADD categorie_service_id INT DEFAULT NULL, ADD collaborateur_id INT DEFAULT NULL, DROP paysId, DROP lieuId, DROP devisesId, DROP categorieId, DROP collaborateurId');
        $this->addSql('ALTER TABLE reference ADD CONSTRAINT FK_AEA34913658D0DB4 FOREIGN KEY (devises_id) REFERENCES devises (devisesId) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE reference ADD CONSTRAINT FK_AEA349136AB213CC FOREIGN KEY (lieu_id) REFERENCES lieu (lieuId) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE reference ADD CONSTRAINT FK_AEA349137395634A FOREIGN KEY (categorie_service_id) REFERENCES categorie (categorieId) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE reference ADD CONSTRAINT FK_AEA34913A848E3B1 FOREIGN KEY (collaborateur_id) REFERENCES collaborateur (collaborateurId) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_AEA349136AB213CC ON reference (lieu_id)');
        $this->addSql('CREATE INDEX IDX_AEA34913658D0DB4 ON reference (devises_id)');
        $this->addSql('CREATE INDEX IDX_AEA349137395634A ON reference (categorie_service_id)');
        $this->addSql('CREATE INDEX IDX_AEA34913A848E3B1 ON reference (collaborateur_id)');
    }
}
