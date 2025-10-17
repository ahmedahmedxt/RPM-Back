<?php

namespace App\Entity;

use App\Repository\ProjetRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProjetRepository::class)]
class Projet
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $projetLibelle = null;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank]
    private ?string $projetDescription = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $projetReference = null;

    #[ORM\Column(type: 'date')]
    #[Assert\NotBlank]
    private ?\DateTimeInterface $projetDateDemarrage = null;

    #[ORM\Column(type: 'date')]
    #[Assert\NotBlank]
    private ?\DateTimeInterface $projetDateAchevement = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $projetUrlFonctionnel = null;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank]
    private ?string $projetDescriptionServiceEffectivementRendus = null;

    #[ORM\ManyToOne(targetEntity: Lieu::class)]
    #[ORM\JoinColumn(name: "lieu_id", referencedColumnName: "lieuId", nullable: true)]
    private ?Lieu $lieu;
    
    #[ORM\ManyToOne(targetEntity: Client::class)]
    #[ORM\JoinColumn(name: "client_id", referencedColumnName: "clientId", nullable: true)]
    private ?Client $client;

    #[ORM\ManyToMany(targetEntity: Categorie::class, inversedBy: 'projets')]
    #[ORM\JoinTable(
        name: 'projet_categorie',
        joinColumns: [new ORM\JoinColumn(name: 'projet_id', referencedColumnName: 'id')],
        inverseJoinColumns: [new ORM\JoinColumn(name: 'categorie_id', referencedColumnName: 'categorieId')]
    )]
    private Collection $categories;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProjetLibelle(): ?string
    {
        return $this->projetLibelle;
    }

    public function setProjetLibelle(string $projetLibelle): self
    {
        $this->projetLibelle = $projetLibelle;
        return $this;
    }

    public function getProjetDescription(): ?string
    {
        return $this->projetDescription;
    }

    public function setProjetDescription(?string $projetDescription): self
    {
        $this->projetDescription = $projetDescription;
        return $this;
    }

    public function getProjetReference(): ?string
    {
        return $this->projetReference;
    }

    public function setProjetReference(string $projetReference): self
    {
        $this->projetReference = $projetReference;
        return $this;
    }

    public function getProjetDateDemarrage(): ?\DateTimeInterface
    {
        return $this->projetDateDemarrage;
    }

    public function setProjetDateDemarrage(\DateTimeInterface $projetDateDemarrage): self
    {
        $this->projetDateDemarrage = $projetDateDemarrage;
        return $this;
    }

    public function getProjetDateAchevement(): ?\DateTimeInterface
    {
        return $this->projetDateAchevement;
    }

    public function setProjetDateAchevement(\DateTimeInterface $projetDateAchevement): self
    {
        $this->projetDateAchevement = $projetDateAchevement;
        return $this;
    }

    public function getProjetUrlFonctionnel(): ?string
    {
        return $this->projetUrlFonctionnel;
    }

    public function setProjetUrlFonctionnel(string $projetUrlFonctionnel): self
    {
        $this->projetUrlFonctionnel = $projetUrlFonctionnel;
        return $this;
    }

    public function getProjetDescriptionServiceEffectivementRendus(): ?string
    {
        return $this->projetDescriptionServiceEffectivementRendus;
    }

    public function setProjetDescriptionServiceEffectivementRendus(string $projetDescriptionServiceEffectivementRendus): self
    {
        $this->projetDescriptionServiceEffectivementRendus = $projetDescriptionServiceEffectivementRendus;
        return $this;
    }

    public function getLieu(): ?Lieu
    {
        return $this->lieu;
    }

    public function setLieu(?Lieu $lieu): self
    {
        $this->lieu = $lieu;
        return $this;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): self
    {
        $this->client = $client;
        return $this;
    }

    /**
     * @return Collection|Categorie[]
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(Categorie $category): self
    {
        if (!$this->categories->contains($category)) {
            $this->categories[] = $category;
        }
        return $this;
    }

    public function removeCategory(Categorie $category): self
    {
        $this->categories->removeElement($category);
        return $this;
    }
}