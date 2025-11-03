<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
#[ORM\Table(name: 'appel_offre_partenaire')]
class AppelOffrePartenaire
{     
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: AppelOffre::class, inversedBy: 'appelOffrePartenaires')]
    #[ORM\JoinColumn(name: 'appel_offre_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?AppelOffre $appelOffre = null;

    #[ORM\ManyToOne(targetEntity: Partenaire::class, inversedBy: 'appelOffrePartenaires')]
    #[ORM\JoinColumn(name: 'partenaire_id', referencedColumnName: 'partenaireId', nullable: false, onDelete: 'CASCADE')]
    #[Groups(['appeloffre:read'])]
    private ?Partenaire $partenaire = null;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    #[Groups(['appeloffre:read'])]
    private ?string $role = null;

    // Getters et Setters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAppelOffre(): ?AppelOffre
    {
        return $this->appelOffre;
    }

    public function setAppelOffre(?AppelOffre $appelOffre): self
    {
        $this->appelOffre = $appelOffre;
        return $this;
    }

    public function getPartenaire(): ?Partenaire
    {
        return $this->partenaire;
    }

    public function setPartenaire(?Partenaire $partenaire): self
    {
        $this->partenaire = $partenaire;
        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(?string $role): self
    {
        $this->role = $role;
        return $this;
    }
}