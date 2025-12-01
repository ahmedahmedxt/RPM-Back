<?php

namespace App\Entity;

use App\Repository\ReferenceCaracteristiqueSpecialeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReferenceCaracteristiqueSpecialeRepository::class)]
#[ORM\Table(name: "referenceCaracteristiqueSpeciale")]
class ReferenceCaracteristiqueSpeciale
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: "referenceCaracteristiqueSpecialeId")]
    private ?int $referenceCaracteristiqueSpecialeId = null;

    #[ORM\Column(name: "referenceCaracteristiqueSpecialeTitre", length: 255, nullable: true)]
    private ?string $referenceCaracteristiqueSpecialeTitre = null;

    #[ORM\Column(name: "referenceCaracteristiqueSpecialeDescription", length: 1000, nullable: true)]
    private ?string $referenceCaracteristiqueSpecialeDescription = null;

    #[ORM\ManyToMany(targetEntity: Reference::class, inversedBy: 'referenceCaracteristiqueSpeciales')]
    #[ORM\JoinTable(name: 'referencereferencecaracteristiquespeciale')]
    #[ORM\JoinColumn(name: 'referenceCaracteristiqueSpecialeId', referencedColumnName: 'referenceCaracteristiqueSpecialeId')]
    #[ORM\InverseJoinColumn(name: 'referenceID', referencedColumnName: 'referenceID')]
    private Collection $references;

    public function __construct()
    {
        $this->references = new ArrayCollection();
    }

    public function getReferenceCaracteristiqueSpecialeId(): ?int
    {
        return $this->referenceCaracteristiqueSpecialeId;
    }

    public function getReferenceCaracteristiqueSpecialeTitre(): ?string
    {
        return $this->referenceCaracteristiqueSpecialeTitre;
    }

    public function setReferenceCaracteristiqueSpecialeTitre(?string $referenceCaracteristiqueSpecialeTitre): self
    {
        $this->referenceCaracteristiqueSpecialeTitre = $referenceCaracteristiqueSpecialeTitre;
        return $this;
    }

    public function getReferenceCaracteristiqueSpecialeDescription(): ?string
    {
        return $this->referenceCaracteristiqueSpecialeDescription;
    }

    public function setReferenceCaracteristiqueSpecialeDescription(?string $referenceCaracteristiqueSpecialeDescription): self
    {
        $this->referenceCaracteristiqueSpecialeDescription = $referenceCaracteristiqueSpecialeDescription;
        return $this;
    }

    public function getReferences(): Collection
    {
        return $this->references;
    }

    public function addReference(Reference $reference): self
    {
        if (!$this->references->contains($reference)) {
            $this->references[] = $reference;
            $reference->addReferenceCaracteristiqueSpeciale($this);
        }

        return $this;
    }

    public function removeReference(Reference $reference): self
    {
        if ($this->references->removeElement($reference)) {
            $reference->removeReferenceCaracteristiqueSpeciale($this);
        }

        return $this;
    }
}