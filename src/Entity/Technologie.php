<?php

namespace App\Entity;

use App\Repository\TechnologieRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: TechnologieRepository::class)]
#[ORM\Table(name: "technologie")]
class Technologie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer", name: "technologieId")]
    private ?int $technologieId= null;


    #[ORM\Column(type: "string", length: 254, nullable: true, name: "referenceTechnologieLibelle")]
    private $referenceTechnologieLibelle;

    #[ORM\Column(type: "text", length: 1000, nullable: true, name: "referenceTechnologieDescription")]
    private $referenceTechnologieDescription;

    #[ORM\ManyToMany(targetEntity: Reference::class, mappedBy: 'technologies')]
    private Collection $references;

    public function __construct()
    {
        $this->references = new ArrayCollection();
    }

    public function getTechnologieId(): ?int
    {
        return $this->technologieId;
    }

    public function getReferenceTechnologieLibelle(): ?string
    {
        return $this->referenceTechnologieLibelle;
    }

    public function getReferenceTechnologieDescription(): ?string
    {
        return $this->referenceTechnologieDescription;
    }


    public function setTechnologieId(?int $technologieId): void
    {
        $this->technologieId = $technologieId;
    }

    public function setReferenceTechnologieLibelle(?string $referenceTechnologieLibelle): void
    {
        $this->referenceTechnologieLibelle = $referenceTechnologieLibelle;
    }

    public function setReferenceTechnologieDescription(?string $referenceTechnologieDescription): void
    {
        $this->referenceTechnologieDescription = $referenceTechnologieDescription;
    }

    public function getReferences(): Collection
    {
        return $this->references;
    }

    public function addReference(Reference $reference): self
    {
        if (!$this->references->contains($reference)) {
            $this->references[] = $reference;
            $reference->addTechnologie($this);
        }

        return $this;
    }

    public function removeReference(Reference $reference): self
    {
        if ($this->references->removeElement($reference)) {
            $reference->removeTechnologie($this);
        }

        return $this;
    }
}
