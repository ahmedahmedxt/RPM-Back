<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251208112227 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE appel_offres CHANGE appelOffresNumeroDevisParticipation appelOffresNumeroDevisParticipation INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE appel_offres CHANGE appelOffresNumeroDevisParticipation appelOffresNumeroDevisParticipation VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE client_pays DROP FOREIGN KEY FK_E503FFCC19EB6921');
        $this->addSql('ALTER TABLE client_pays DROP FOREIGN KEY FK_E503FFCCA6E44244');
    }
}
