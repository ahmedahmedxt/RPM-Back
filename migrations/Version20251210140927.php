<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251210140927 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sourceappeloffres CHANGE sourceAppelOffresLibelle sourceAppelOffresLibelle VARCHAR(50) NOT NULL, CHANGE sourceAppelOffresUrl sourceAppelOffresUrl VARCHAR(50) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE client_pays DROP FOREIGN KEY FK_E503FFCC19EB6921');
        $this->addSql('ALTER TABLE client_pays DROP FOREIGN KEY FK_E503FFCCA6E44244');
        $this->addSql('ALTER TABLE sourceappeloffres CHANGE sourceAppelOffresLibelle sourceAppelOffresLibelle VARCHAR(255) NOT NULL, CHANGE sourceAppelOffresUrl sourceAppelOffresUrl VARCHAR(255) DEFAULT NULL');
    }
}
