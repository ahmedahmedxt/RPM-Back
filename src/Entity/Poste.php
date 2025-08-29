<?php

namespace App\Entity;

use App\Repository\PosteRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\DBAL\Types\Types;


#[ORM\Entity(repositoryClass: PosteRepository::class)]
class Poste
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Assert\NotBlank]
    private ?string $posteNom = null;

    #[ORM\OneToMany(targetEntity: Employe::class, mappedBy: 'poste',cascade: ["persist","remove"])]
    private Collection $employes;

    /*
    #[ORM\OneToMany(targetEntity: ProjetEmployePoste::class, mappedBy: 'poste',cascade: ["persist","remove"])]
    private Collection $projetsEmployePostes ;
    */

    public function __construct()
    {
        $this->employes = new ArrayCollection();
        //$this->projetsEmployePostes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPosteNom(): ?string
    {
        return $this->posteNom;
    }

    public function setPosteNom(string $posteNom): static
    {
        $this->posteNom = $posteNom;

        return $this;
    }
    /**
     * @return Collection|Employe[]
    */
    public function getEmployes(): Collection
    {
        return $this->employes;
    }

    public function addEmploye(Employe $employe): self
    {
        if (!$this->employes->contains($employe)) {
            $this->employes[] = $employe;
            $employe->setPoste($this); // Assurez-vous de définir le poste sur l'employé
        }
    
        return $this;
    }
    

    public function removeEmploye(Employe $employe): self
    {
        if ($this->employes->removeElement($employe)) {
            // set the owning side to null (unless already changed)
            if ($employe->getPoste() === $this) {
                $employe->setPoste(null);
            }
        }

        return $this;
    }
    /**
     * @return Collection|ProjetEmployePoste[]
     */
    public function getProjetEmployePostes()
    {
        return $this->projetEmployePostes;
    }

    public function addProjetEmployePoste(ProjetEmployePoste $projetEmployePoste): self
    {
        if (!$this->projetEmployePostes->contains($projetEmployePoste)) {
            $this->projetEmployePostes[] = $projetEmployePoste;
            $projetEmployePoste->setPoste($this);
        }

        return $this;
    }

    public function removeProjetEmployePoste(ProjetEmployePoste $projetEmployePoste): self
    {
        if ($this->projetEmployePostes->removeElement($projetEmployePoste)) {
            // Définit le côté propriétaire à null (sauf si déjà défini)
            if ($projetEmployePoste->getPoste() === $this) {
                $projetEmployePoste->setPoste(null);
            }
        }

        return $this;
    }
}