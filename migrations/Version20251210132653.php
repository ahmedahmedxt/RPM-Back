<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251210132653 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sourceappeloffres ADD paysId INT DEFAULT NULL');
        $this->addSql('ALTER TABLE sourceappeloffres ADD CONSTRAINT FK_40BB23AC35569A8D FOREIGN KEY (paysId) REFERENCES pays (paysId)');
        $this->addSql('CREATE INDEX IDX_40BB23AC35569A8D ON sourceappeloffres (paysId)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE client_pays DROP FOREIGN KEY FK_E503FFCC19EB6921');
        $this->addSql('ALTER TABLE client_pays DROP FOREIGN KEY FK_E503FFCCA6E44244');
        $this->addSql('ALTER TABLE sourceappeloffres DROP FOREIGN KEY FK_40BB23AC35569A8D');
        $this->addSql('DROP INDEX IDX_40BB23AC35569A8D ON sourceappeloffres');
        $this->addSql('ALTER TABLE sourceappeloffres DROP paysId');
    }
}
