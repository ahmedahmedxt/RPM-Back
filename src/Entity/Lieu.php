<?php

namespace App\Entity;

use App\Repository\LieuRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: LieuRepository::class)]
class Lieu
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer',name:'lieuId')]
    private ?int $lieuId;

    #[ORM\Column(length: 255, unique: true,name:'lieuLibelle')]
    #[Assert\NotBlank]
    private ?string $lieuLibelle = null;

    #[ORM\ManyToOne(targetEntity: Pays::class)]
    #[ORM\JoinColumn(name: "paysId", referencedColumnName: "paysId", nullable: true)]
    private ?Pays $pays = null;

    #[ORM\OneToMany(targetEntity: Reference::class, mappedBy: "lieu")]

    private $references;

    public function __construct()
    {
        $this->references = new ArrayCollection();
    }

    public function getLieuId(): ?int
    {
        return $this->lieuId;
    }

    public function getLieuLibelle(): ?string
    {
        return $this->lieuLibelle;
    }

    public function setLieuLibelle(string $lieuNom): self
    {
        $this->lieuLibelle = $lieuNom;

        return $this;
    }

    public function getPays(): ?Pays
    {
        return $this->pays;
    }

    public function setPays(?Pays $pays): self
    {
        $this->pays = $pays;

        return $this;
    }

    /**
     * @return Collection|Reference[]
     */
    public function getReferences(): Collection
    {
        return $this->references;
    }

    public function addReference(Reference $reference): self
    {
        if (!$this->references->contains($reference)) {
            $this->references[] = $reference;
            $reference->setLieu($this);
        }

        return $this;
    }

    public function removeReference(Reference $reference): self
    {
        if ($this->references->removeElement($reference)) {
            // set the owning side to null (unless already changed)
            if ($reference->getLieu() === $this) {
                $reference->setLieu(null);
            }
        }

        return $this;
    }
}
