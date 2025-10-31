<?php

namespace App\Entity;

use App\Repository\DevisesRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: DevisesRepository::class)]
#[ORM\Table(name: "devises")]
class Devises
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer", name: "devisesId")]
    private ?int $devisesId = null;

    #[ORM\Column(type: "string", length: 254, nullable: true, name: "devisesLibelle")]
    private ?string $devisesLibelle = null;

    #[ORM\Column(type: "string", length: 10, nullable: true, name: "devisesAcronyme")]
    private ?string $devisesAcronyme = null;

    #[ORM\OneToMany(targetEntity: Reference::class, mappedBy: "devises")]
    private $references;

    // Relation inverse vers AppelOffres: propriété côté AppelOffres = "appelOffresDevisesId"
    #[ORM\OneToMany(targetEntity: AppelOffres::class, mappedBy: 'appelOffresDevisesId')]
    private Collection $appelOffres;

    public function __construct()
    {
        $this->references = new ArrayCollection();
        $this->appelOffres = new ArrayCollection();
    }

    public function getDevisesId(): ?int
    {
        return $this->devisesId;
    }

    public function getDevisesLibelle(): ?string
    {
        return $this->devisesLibelle;
    }

    public function setDevisesLibelle(?string $devisesLibelle): self
    {
        $this->devisesLibelle = $devisesLibelle;
        return $this;
    }

    public function getDevisesAcronyme(): ?string
    {
        return $this->devisesAcronyme;
    }

    public function setDevisesAcronyme(?string $devisesAcronyme): self
    {
        $this->devisesAcronyme = $devisesAcronyme;
        return $this;
    }

    /**
     * @return Collection<int, AppelOffres>
     */
    public function getAppelOffres(): Collection
    {
        return $this->appelOffres;
    }

    public function addAppelOffres(AppelOffres $appel): self
    {
        if (!$this->appelOffres->contains($appel)) {
            $this->appelOffres->add($appel);
            // côté AppelOffres: propriété "appelOffresDevisesId"
            $appel->setAppelOffresDevisesId($this);
        }
        return $this;
    }

    public function removeAppelOffres(AppelOffres $appel): self
    {
        if ($this->appelOffres->removeElement($appel)) {
            if ($appel->getAppelOffresDevisesId() === $this) {
                $appel->setAppelOffresDevisesId(null);
            }
        }
        return $this;
    }
}