<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'appel_offres_personnel_cle_appel_offres')]
class AppelOffresPersonnelCleAppelOffres
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: AppelOffres::class, inversedBy: 'appelOffresPersonnelCleAppelOffres')]
    #[ORM\JoinColumn(name: 'appelOffresId', referencedColumnName: 'appelOffresId', nullable: false, onDelete: 'CASCADE')]
    private ?AppelOffres $appelOffres = null;
    
    #[ORM\Column(type: 'integer', nullable: true, name: 'ordreAffichage')]
    private ?int $ordreAffichage = null;
    
    #[ORM\ManyToOne(targetEntity: AppelOffresPersonnelCle::class, inversedBy: 'appelOffresPersonnelCleAppelOffres')]
    #[ORM\JoinColumn(name: 'appelOffresPersonnelCleId', referencedColumnName: 'appelOffresPersonnelCleId', nullable: false, onDelete: 'CASCADE')]
    private ?AppelOffresPersonnelCle $appelOffresPersonnelCle = null;

    #[ORM\Column(type: 'string', length: 10, nullable: true, name: 'couleurStatus')]
    private ?string $couleurStatus = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrdreAffichage(): ?int
    {
        return $this->ordreAffichage;
    }

    public function setOrdreAffichage(?int $ordreAffichage): self
    {
        $this->ordreAffichage = $ordreAffichage;
        return $this;
    }

    public function getAppelOffres(): ?AppelOffres
    {
        return $this->appelOffres;
    }

    public function setAppelOffres(?AppelOffres $appelOffres): self
    {
        $this->appelOffres = $appelOffres;
        return $this;
    }

    public function getAppelOffresPersonnelCle(): ?AppelOffresPersonnelCle
    {
        return $this->appelOffresPersonnelCle;
    }

    public function setAppelOffresPersonnelCle(?AppelOffresPersonnelCle $appelOffresPersonnelCle): self
    {
        $this->appelOffresPersonnelCle = $appelOffresPersonnelCle;
        return $this;
    }

    public function getCouleurStatus(): ?string
    {
        return $this->couleurStatus;
    }

    public function setCouleurStatus(?string $couleurStatus): self
    {
        $this->couleurStatus = $couleurStatus;
        return $this;
    }
}