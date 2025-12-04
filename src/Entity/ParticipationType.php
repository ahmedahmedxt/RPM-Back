<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
#[ORM\Table(name: 'participationtype')]
class ParticipationType
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'participationTypeId', type: 'integer')]
    private ?int $participationTypeId = null;

    #[ORM\Column(name: 'participationTypeLibelle', type: 'string', length: 100)]
    private ?string $participationTypeLibelle = null;

    #[ORM\OneToMany(targetEntity: AppelOffres::class, mappedBy: 'appelOffresTypeParticipationId')]
    private Collection $appelOffres;

    public function __construct()
    {
        $this->appelOffres = new ArrayCollection();
    }

    public function getParticipationTypeId(): ?int { return $this->participationTypeId; }

    public function getParticipationTypeLibelle(): ?string { return $this->participationTypeLibelle; }
    public function setParticipationTypeLibelle(?string $v): self { $this->participationTypeLibelle = $v; return $this; }

    public function __toString(): string
    {
        return (string) ($this->participationTypeLibelle ?? '');
    }
}