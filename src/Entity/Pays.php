<?php

namespace App\Entity;

use App\Repository\PaysRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\AppelOffres;

#[ORM\Entity(repositoryClass: PaysRepository::class)]
class Pays
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: "paysId", type: "integer")]
    private ?int $id = null;

    #[ORM\Column(name: "paysLibelle", length: 254, unique: true)]
    #[Assert\NotBlank]
    private ?string $paysLibelle = null;

    #[ORM\Column(name: "paysCapitale", length: 254, unique: true)]
    #[Assert\NotBlank]
    private ?string $paysCapitale = null;

    #[ORM\ManyToOne(targetEntity: Continent::class)]
    #[ORM\JoinColumn(name: "continentId", referencedColumnName: "continentId")]
    private ?Continent $continent = null;

    #[ORM\OneToMany(targetEntity: Lieu::class, mappedBy: "pays")]
    private Collection $lieux;

    #[ORM\OneToMany(targetEntity: Client::class, mappedBy: 'pays')]
    private Collection $clients; 

    #[ORM\OneToMany(targetEntity: AppelOffres::class, mappedBy: 'appelOffresPaysId')]
    private Collection $appelOffres;

    #[ORM\OneToMany(targetEntity: EmployeExperience::class, mappedBy: 'pays')]
    private Collection $employeExperiences;

    public function __construct()
    {
        $this->lieux = new ArrayCollection();
        $this->clients = new ArrayCollection();
        $this->appelOffres = new ArrayCollection();
        $this->employeExperiences = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    // Ajout de getPaysId() pour cohérence avec le contrôleur
    public function getPaysId(): ?int
    {
        return $this->id;
    }

    public function getPaysLibelle(): ?string
    {
        return $this->paysLibelle;
    }

    public function setPaysLibelle(string $paysLibelle): static
    {
        $this->paysLibelle = $paysLibelle;
        return $this;
    }

    public function getPaysCapitale(): ?string
    {
        return $this->paysCapitale;
    }

    public function setPaysCapitale(string $paysCapitale): static
    {
        $this->paysCapitale = $paysCapitale;
        return $this;
    }

    public function getContinent(): ?Continent
    {
        return $this->continent;
    }

    public function setContinent(?Continent $continent): static
    {
        $this->continent = $continent;
        return $this;
    }

    /**
     * @return Collection|Lieu[]
     */
    public function getLieux(): Collection
    {
        return $this->lieux;
    }

    public function addLieu(Lieu $lieu): self
    {
        if (!$this->lieux->contains($lieu)) {
            $this->lieux[] = $lieu;
            $lieu->setPays($this);
        }
        return $this;
    }

    public function removeLieu(Lieu $lieu): self
    {
        if ($this->lieux->removeElement($lieu)) {
            if ($lieu->getPays() === $this) {
                $lieu->setPays(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, Client>
     */
    public function getClients(): Collection
    {
        return $this->clients;
    }

    public function addClient(Client $client): static
    {
        if (!$this->clients->contains($client)) {
            $this->clients->add($client);
            $client->setPays($this);
        }
        return $this;
    }

    public function removeClient(Client $client): static
    {
        if ($this->clients->removeElement($client)) {
            if ($client->getPays() === $this) {
                $client->setPays(null);
            }
        }
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
            $appel->setAppelOffresPaysId($this);
        }
        return $this;
    }

    public function removeAppelOffres(AppelOffres $appel): self
    {
        if ($this->appelOffres->removeElement($appel)) {
            if ($appel->getAppelOffresPaysId() === $this) {
                $appel->setAppelOffresPaysId(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, EmployeExperience>
     */
    public function getEmployeExperiences(): Collection
    {
        return $this->employeExperiences;
    }

    public function addEmployeExperience(EmployeExperience $employeExperience): static
    {
        if (!$this->employeExperiences->contains($employeExperience)) {
            $this->employeExperiences->add($employeExperience);
            $employeExperience->setPays($this);
        }
        return $this;
    }

    public function removeEmployeExperience(EmployeExperience $employeExperience): static
    {
        if ($this->employeExperiences->removeElement($employeExperience)) {
            if ($employeExperience->getPays() === $this) {
                $employeExperience->setPays(null);
            }
        }
        return $this;
    }
}