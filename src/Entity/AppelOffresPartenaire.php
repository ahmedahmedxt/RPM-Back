<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
#[ORM\Table(name: 'appel_offres_partenaires')]
class AppelOffresPartenaire
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: AppelOffres::class, inversedBy: 'appelOffresPartenaires')]
    #[ORM\JoinColumn(name: 'appelOffresId', referencedColumnName: 'appelOffresId', nullable: false, onDelete: 'CASCADE')]
    private ?AppelOffres $appelOffres = null;

    #[ORM\ManyToOne(targetEntity: Partenaire::class, inversedBy: 'appelOffresPartenaires')]
    #[ORM\JoinColumn(name: 'partenaire_id', referencedColumnName: 'partenaireId', nullable: false, onDelete: 'CASCADE')]
    #[Groups(['appeloffres:read'])]
    private ?Partenaire $partenaire = null;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    #[Groups(['appeloffres:read'])]
    private ?string $role = null;

    public function getId(): ?int { return $this->id; }

    public function getAppelOffres(): ?AppelOffres { return $this->appelOffres; }
    public function setAppelOffres(?AppelOffres $appelOffres): self { $this->appelOffres = $appelOffres; return $this; }

    public function getPartenaire(): ?Partenaire { return $this->partenaire; }
    public function setPartenaire(?Partenaire $partenaire): self { $this->partenaire = $partenaire; return $this; }

    public function getRole(): ?string { return $this->role; }
    public function setRole(?string $role): self { $this->role = $role; return $this; }
}