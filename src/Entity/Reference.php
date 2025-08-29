<?php

namespace App\Entity;

use App\Repository\ReferenceRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: ReferenceRepository::class)]
#[ORM\Table(name: "reference")]
class Reference
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer", name: "referenceID")]
    private ?int $referenceID = null;

    #[ORM\ManyToOne(targetEntity: Client::class)]
    #[ORM\JoinColumn(name: "clientId", referencedColumnName: "clientId", nullable: false)]
    private $client;

    #[ORM\ManyToOne(targetEntity: Devises::class)]
    #[ORM\JoinColumn(name: "devisesId", referencedColumnName: "devisesId", nullable: false)]
    private $devises;

    #[ORM\ManyToOne(targetEntity: Lieu::class)]
    #[ORM\JoinColumn(name: "lieuId", referencedColumnName: "lieuId", nullable: false)]
    private $lieu;

    #[ORM\ManyToOne(targetEntity: Categorie::class)]
    #[ORM\JoinColumn(name: "categorieId", referencedColumnName: "categorieId", nullable: false)]
    #[Assert\NotBlank]
    private $categorie;

    #[ORM\Column(type: "string", length: 254, nullable: true, name: "referenceRef")]
    private $referenceRef;

    #[ORM\Column(type: "string", length: 254, nullable: true, name: "referenceTitre")]
    private $referenceTitre;

    #[ORM\Column(type: "string", length: 254, nullable: true, name: "referenceLibelle")]
    private $referenceLibelle;

    #[ORM\Column(type: "string", length: 254, nullable: true, name: "referenceUrlFonctionnel")]
    private $referenceUrlFonctionnel;

    #[ORM\Column(type: "integer", nullable: true, name: "referenceDuree")]
    private $referenceDuree;

    #[ORM\Column(type: "datetime", nullable: true, name: "referenceDateDemarrage")]
    private $referenceDateDemarrage;

    #[ORM\Column(type: "datetime", nullable: true, name: "referenceDateAchevement")]
    private $referenceDateAchevement;

    #[ORM\Column(type: "integer", nullable: true, name: "referenceAnneeAchevement")]
    private $referenceAnneeAchevement;

    #[ORM\Column(type: "datetime", nullable: true, name: "referenceDateReceptionProvisoire")]
    private $referenceDateReceptionProvisoire;

    #[ORM\Column(type: "datetime", nullable: true, name: "referenceDateReceptionDefinitive")]
    private $referenceDateReceptionDefinitive;

    #[ORM\Column(type: "string", length: 1000, nullable: true, name: "referenceCaracteristiques")]
    private $referenceCaracteristiques;

    #[ORM\Column(type: "string", length: 1000, nullable: true, name: "referenceDescription")]
    private $referenceDescription;

    #[ORM\Column(type: "string", length: 1000, nullable: true, name: "referenceDescriptionServiceEffectivemenetRendus")]
    private $referenceDescriptionServiceEffectivemenetRendus;

    #[ORM\Column(type: "integer", nullable: true, name: "referenceDureeGarantie")]
    private $referenceDureeGarantie;

    #[ORM\Column(type: "float", nullable: true, name: "referenceBudget")]
    private $referenceBudget;

    #[ORM\Column(type: "string", length: 100, nullable: true, name: "referencePartBudgetGroupement")]
    private $referencePartBudgetGroupement;

    #[ORM\Column(type: "string", length: 1000, nullable: true, name: "referenceRemarque")]
    private $referenceRemarque;

    #[ORM\ManyToMany(targetEntity: BailleurFond::class, inversedBy: 'references')]
    #[ORM\JoinTable(name: 'referencebailleurfond')]
    #[ORM\JoinColumn(name: 'referenceID', referencedColumnName: 'referenceID')]
    #[ORM\InverseJoinColumn(name: 'bailleurFondId', referencedColumnName: 'bailleurFondId')]
    private Collection $bailleurfonds;

    #[ORM\ManyToMany(targetEntity: Role::class, inversedBy: 'references')]
    #[ORM\JoinTable(name: 'referencerole')]
    #[ORM\JoinColumn(name: 'referenceID', referencedColumnName: 'referenceID')]
    #[ORM\InverseJoinColumn(name: 'roleId', referencedColumnName: 'roleId')]
    private Collection $roles;

    #[ORM\ManyToMany(targetEntity: Technologie::class, inversedBy: 'references')]
    #[ORM\JoinTable(name: 'referencetechnologie')]
    #[ORM\JoinColumn(name: 'referenceID', referencedColumnName: 'referenceID')]
    #[ORM\InverseJoinColumn(name: 'technologieId', referencedColumnName: 'technologieId')]
    private Collection $technologies;

    #[ORM\ManyToMany(targetEntity: Methodologie::class, inversedBy: 'references')]
    #[ORM\JoinTable(name: 'referencemethodologie')]
    #[ORM\JoinColumn(name: 'referenceID', referencedColumnName: 'referenceID')]
    #[ORM\InverseJoinColumn(name: 'methodologieId', referencedColumnName: 'methodologieId')]
    private Collection $methodologies;

    #[ORM\ManyToMany(targetEntity: EnvironnementDeveloppement::class, inversedBy: 'references')]
    #[ORM\JoinTable(name: 'referenceenvironnementdeveloppement')]
    #[ORM\JoinColumn(name: 'referenceID', referencedColumnName: 'referenceID')]
    #[ORM\InverseJoinColumn(name: 'environnementDeveloppementId', referencedColumnName: 'environnementDeveloppementId')]
    private Collection $environnementdeveloppements;

    public function __construct()
    {
        $this->bailleurfonds = new ArrayCollection();
        $this->roles = new ArrayCollection();
        $this->technologies = new ArrayCollection();
        $this->methodologies = new ArrayCollection();
        $this->environnementdeveloppements = new ArrayCollection();
    }

    public function getReferenceID(): ?int
    {
        return $this->referenceID;
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

    public function getDevises(): ?Devises
    {
        return $this->devises;
    }

    public function setDevises(?Devises $devises): self
    {
        $this->devises = $devises;
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

    public function getCategorie(): ?Categorie
    {
        return $this->categorie;
    }

    public function setCategorie(?Categorie $categorie): self
    {
        $this->categorie = $categorie;
        return $this;
    }

    public function getReferenceRef(): ?string
    {
        return $this->referenceRef;
    }

    public function setReferenceRef(?string $referenceRef): self
    {
        $this->referenceRef = $referenceRef;
        return $this;
    }

    public function getReferenceTitre(): ?string
    {
        return $this->referenceTitre;
    }

    public function setReferenceTitre(?string $referenceTitre): self
    {
        $this->referenceTitre = $referenceTitre;
        return $this;
    }

    public function getReferenceLibelle(): ?string
    {
        return $this->referenceLibelle;
    }

    public function setReferenceLibelle(?string $referenceLibelle): self
    {
        $this->referenceLibelle = $referenceLibelle;
        return $this;
    }

    public function getReferenceUrlFonctionnel(): ?string
    {
        return $this->referenceUrlFonctionnel;
    }

    public function setReferenceUrlFonctionnel(?string $referenceUrlFonctionnel): self
    {
        $this->referenceUrlFonctionnel = $referenceUrlFonctionnel;
        return $this;
    }

    public function getReferenceDuree(): ?int
    {
        return $this->referenceDuree;
    }

    public function setReferenceDuree(?int $referenceDuree): self
    {
        $this->referenceDuree = $referenceDuree;
        return $this;
    }

    public function getReferenceDateDemarrage(): ?\DateTimeInterface
    {
        return $this->referenceDateDemarrage;
    }

    public function setReferenceDateDemarrage(?\DateTimeInterface $referenceDateDemarrage): self
    {
        $this->referenceDateDemarrage = $referenceDateDemarrage;
        return $this;
    }

    public function getReferenceDateAchevement(): ?\DateTimeInterface
    {
        return $this->referenceDateAchevement;
    }

    public function setReferenceDateAchevement(?\DateTimeInterface $referenceDateAchevement): self
    {
        $this->referenceDateAchevement = $referenceDateAchevement;
        return $this;
    }

    public function getReferenceAnneeAchevement(): ?int
    {
        return $this->referenceAnneeAchevement;
    }

    public function setReferenceAnneeAchevement(?int $referenceAnneeAchevement): self
    {
        $this->referenceAnneeAchevement = $referenceAnneeAchevement;
        return $this;
    }

    public function getReferenceDateReceptionProvisoire(): ?\DateTimeInterface
    {
        return $this->referenceDateReceptionProvisoire;
    }

    public function setReferenceDateReceptionProvisoire(?\DateTimeInterface $referenceDateReceptionProvisoire): self
    {
        $this->referenceDateReceptionProvisoire = $referenceDateReceptionProvisoire;
        return $this;
    }

    public function getReferenceDateReceptionDefinitive(): ?\DateTimeInterface
    {
        return $this->referenceDateReceptionDefinitive;
    }

    public function setReferenceDateReceptionDefinitive(?\DateTimeInterface $referenceDateReceptionDefinitive): self
    {
        $this->referenceDateReceptionDefinitive = $referenceDateReceptionDefinitive;
        return $this;
    }

    public function getReferenceCaracteristiques(): ?string
    {
        return $this->referenceCaracteristiques;
    }

    public function setReferenceCaracteristiques(?string $referenceCaracteristiques): self
    {
        $this->referenceCaracteristiques = $referenceCaracteristiques;
        return $this;
    }

    public function getReferenceDescription(): ?string
    {
        return $this->referenceDescription;
    }

    public function setReferenceDescription(?string $referenceDescription): self
    {
        $this->referenceDescription = $referenceDescription;
        return $this;
    }

    public function getReferenceDescriptionServiceEffectivemenetRendus(): ?string
    {
        return $this->referenceDescriptionServiceEffectivemenetRendus;
    }

    public function setReferenceDescriptionServiceEffectivemenetRendus(?string $referenceDescriptionServiceEffectivementRendus): self
    {
        $this->referenceDescriptionServiceEffectivemenetRendus = $referenceDescriptionServiceEffectivementRendus;
        return $this;
    }

    public function getReferenceDureeGarantie(): ?int
    {
        return $this->referenceDureeGarantie;
    }

    public function setReferenceDureeGarantie(?int $referenceDureeGarantie): self
    {
        $this->referenceDureeGarantie = $referenceDureeGarantie;
        return $this;
    }

    public function getReferenceBudget(): ?float
    {
        return $this->referenceBudget;
    }

    public function setReferenceBudget(?float $referenceBudget): self
    {
        $this->referenceBudget = $referenceBudget;
        return $this;
    }

    public function getReferencePartBudgetGroupement(): ?string
    {
        return $this->referencePartBudgetGroupement;
    }

    public function setReferencePartBudgetGroupement(?string $referencePartBudgetGroupement): void
    {
        $this->referencePartBudgetGroupement = $referencePartBudgetGroupement;
    }

    public function getReferenceRemarque(): ?string
    {
        return $this->referenceRemarque;
    }

    public function setReferenceRemarque(?string $referenceRemarque): void
    {
        $this->referenceRemarque = $referenceRemarque;
    }

    // For BailleurFond
    public function getBailleurfonds(): Collection
    {
        return $this->bailleurfonds;
    }

    public function addBailleurfond(BailleurFond $bailleurFond): self
    {
        if (!$this->bailleurfonds->contains($bailleurFond)) {
            $this->bailleurfonds[] = $bailleurFond;
            $bailleurFond->addReference($this);
        }

        return $this;
    }

    public function removeBailleurfond(BailleurFond $bailleurFond): self
    {
        if ($this->bailleurfonds->removeElement($bailleurFond)) {
            $bailleurFond->removeReference($this);
        }

        return $this;
    }

    // For Role
    public function getRoles(): Collection
    {
        return $this->roles;
    }

    public function addRole(Role $role): self
    {
        if (!$this->roles->contains($role)) {
            $this->roles[] = $role;
            $role->addReference($this);
        }

        return $this;
    }

    public function removeRole(Role $role): self
    {
        if ($this->roles->removeElement($role)) {
            $role->removeReference($this);
        }

        return $this;
    }

    // For Technologie
    public function getTechnologies(): Collection
    {
        return $this->technologies;
    }

    public function addTechnologie(Technologie $technologie): self
    {
        if (!$this->technologies->contains($technologie)) {
            $this->technologies[] = $technologie;
            $technologie->addReference($this);
        }

        return $this;
    }

    public function removeTechnologie(Technologie $technologie): self
    {
        if ($this->technologies->removeElement($technologie)) {
            $technologie->removeReference($this);
        }

        return $this;
    }

    // For Methodologie
    public function getMethodologies(): Collection
    {
        return $this->methodologies;
    }

    public function addMethodologie(Methodologie $methodologie): self
    {
        if (!$this->methodologies->contains($methodologie)) {
            $this->methodologies[] = $methodologie;
            $methodologie->addReference($this);
        }

        return $this;
    }

    public function removeMethodologie(Methodologie $methodologie): self
    {
        if ($this->methodologies->removeElement($methodologie)) {
            $methodologie->removeReference($this);
        }

        return $this;
    }

    // For EnvironnementDeveloppement
    public function getEnvironnementdeveloppements(): Collection
    {
        return $this->environnementdeveloppements;
    }

    public function addEnvironnementdeveloppement(EnvironnementDeveloppement $environnementDeveloppement): self
    {
        if (!$this->environnementdeveloppements->contains($environnementDeveloppement)) {
            $this->environnementdeveloppements[] = $environnementDeveloppement;
            $environnementDeveloppement->addReference($this);
        }

        return $this;
    }

    public function removeEnvironnementdeveloppement(EnvironnementDeveloppement $environnementDeveloppement): self
    {
        if ($this->environnementdeveloppements->removeElement($environnementDeveloppement)) {
            $environnementDeveloppement->removeReference($this);
        }

        return $this;
    }
}
