<?php

namespace App\Entity;

use App\Repository\MethodologieRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: MethodologieRepository::class)]
#[ORM\Table(name: "methodologie")]
class Methodologie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer", name: "methodologieId")]
    private ?int $methodologieId = null;

    #[ORM\Column(type: "string", length: 254, nullable: true, name: "methodologieLibelle")]
    private $methodologieLibelle;
    #[ORM\Column(type: "string", length: 254, nullable: true, name: "methodologieDescription")]
    private $methodologieDescription;

    #[ORM\ManyToMany(targetEntity: Reference::class, mappedBy: 'methodologies')]
    private Collection $references;

    public function __construct()
    {
        $this->references = new ArrayCollection();
    }

    public function getMethodologieId(): ?int
    {
        return $this->methodologieId;
    }

    public function getMethodologieLibelle(): ?string
    {
        return $this->methodologieLibelle;
    }

    public function getMethodologieDescription(): ?string
    {
        return $this->methodologieDescription;
    }

    public function setMethodologieLibelle(?string $methodologieLibelle): void
    {
        $this->methodologieLibelle = $methodologieLibelle;
    }

    public function setMethodologieDescription(?string $methodologieDescription): void
    {
        $this->methodologieDescription = $methodologieDescription;
    }

    public function getReferences(): Collection
    {
        return $this->references;
    }

    public function addReference(Reference $reference): self
    {
        if (!$this->references->contains($reference)) {
            $this->references[] = $reference;
            $reference->addMethodologie($this);
        }

        return $this;
    }

    public function removeReference(Reference $reference): self
    {
        if ($this->references->removeElement($reference)) {
            $reference->removeMethodologie($this);
        }

        return $this;
    }

}
