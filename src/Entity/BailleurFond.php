<?php

namespace App\Entity;

use App\Repository\BailleurFondRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: BailleurFondRepository::class)]
#[ORM\Table(name: "bailleurfond")]

class BailleurFond
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer", name: "bailleurFondId")]
    private ?int $bailleurFondId= null;

    #[ORM\Column(type: "string", length: 254, nullable: true, name: "bailleurFondLibelle")]
    private $bailleurFondLibelle;

    #[ORM\Column(type: "string", length: 254, nullable: true, name: "bailleurFondAcronyme")]
    private $bailleurFondAcronyme;

    #[ORM\ManyToMany(targetEntity: Reference::class, mappedBy: 'bailleurfonds')]
    private Collection $references;

    public function __construct()
    {
        $this->references = new ArrayCollection();
    }

    public function getBailleurFondId(): ?int
    {
        return $this->bailleurFondId;
    }

    public function getBailleurFondLibelle(): ?string
    {
        return $this->bailleurFondLibelle;
    }

    public function getBailleurFondAcronyme(): ?string
    {
        return $this->bailleurFondAcronyme;
    }

    // Setters
    public function setBailleurFondId(?int $bailleurFondId): void
    {
        $this->bailleurFondId = $bailleurFondId;
    }

    public function setBailleurFondLibelle(?string $bailleurFondLibelle): void
    {
        $this->bailleurFondLibelle = $bailleurFondLibelle;
    }

    public function setBailleurFondAcronyme(?string $bailleurFondAcronyme): void
    {
        $this->bailleurFondAcronyme = $bailleurFondAcronyme;
    }

    public function getReference(): Collection
    {
        return $this->references;
    }

    public function addReference(Reference $reference): self
    {
        if (!$this->references->contains($reference)) {
            $this->references[] = $reference;
            $reference->addBailleurfond($this);
        }

        return $this;
    }

    public function removeReference(Reference $reference): self
    {
        if ($this->references->contains($reference)) {
            $this->references->removeElement($reference);
            $reference->removeBailleurfond($this);
        }

        return $this;
    }


}
