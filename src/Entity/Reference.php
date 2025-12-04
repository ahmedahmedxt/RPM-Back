<?php

namespace App\Entity;

use App\Repository\ReferenceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReferenceRepository::class)]
#[ORM\Table(name: "reference")]
class Reference
{
    /* =======================
     *  ID
     * ======================= */

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer", name: "referenceID")]
    private ?int $referenceID = null;

    /* =======================
     *  SCALAR FIELDS
     * ======================= */

    #[ORM\Column(type: "string", length: 254, nullable: true, name: "referenceRef")]
    private $referenceRef;

    #[ORM\Column(type: "string", length: 254, nullable: true, name: "referenceTitre")]
    private $referenceTitre;

    #[ORM\Column(type: "string", length: 254, nullable: true, name: "referenceLibelle")]
    private $referenceLibelle;

    #[ORM\Column(type: "string", length: 254, nullable: true, name: "referenceUrlFonctionnel")]
    private $referenceUrlFonctionnel;

    #[ORM\Column(type: "integer", nullable: true, name: "referenceDureeExecution")]
    private $referenceDureeExecution;

    #[ORM\Column(type: "date", nullable: true, name: "referenceDateDemarrage")]
    private $referenceDateDemarrage;

    #[ORM\Column(type: "date", nullable: true, name: "referenceDateAchevement")]
    private $referenceDateAchevement;

    #[ORM\Column(type: "date", nullable: true, name: "referenceDateReceptionProvisoire")]
    private $referenceDateReceptionProvisoire;

    #[ORM\Column(type: "integer", nullable: true, name: "referenceDureeGarantie")]
    private $referenceDureeGarantie;

    #[ORM\Column(type: "date", nullable: true, name: "referenceDateReceptionDefinitive")]
    private $referenceDateReceptionDefinitive;

    #[ORM\Column(type: "text", nullable: true, name: "referenceCaracteristiques")]
    private $referenceCaracteristiques;

    #[ORM\Column(type: "text", nullable: true, name: "referenceDescription")]
    private $referenceDescription;

    #[ORM\Column(type: "text", nullable: true, name: "referenceDescriptionServiceEffectivementRendus")]
    private $referenceDescriptionServiceEffectivementRendus;

    #[ORM\Column(type: "float", nullable: true, name: "referenceBudget")]
    private $referenceBudget;

    #[ORM\Column(type: "string", length: 254, nullable: true, name: "referencePartBudget")]
    private $referencePartBudget;

    #[ORM\Column(type: "text", nullable: true, name: "referenceRemarque")]
    private $referenceRemarque;

    /* =======================
     *  MANY-TO-ONE RELATIONS
     * ======================= */

    #[ORM\ManyToOne(targetEntity: Pays::class)]
    #[ORM\JoinColumn(name: "paysId", referencedColumnName: "paysId", nullable: true)]
    private $pays;

    // lieu 1 ---- * reference
    #[ORM\ManyToOne(targetEntity: Lieu::class)]
    #[ORM\JoinColumn(name: "lieuId", referencedColumnName: "lieuId", nullable: true)]
    private $lieu;

    // devises 1 ---- * reference
    #[ORM\ManyToOne(targetEntity: Devises::class)]
    #[ORM\JoinColumn(name: "devisesId", referencedColumnName: "devisesId", nullable: true)]
    private $devises;

    // categorieService 1 ---- * reference
    #[ORM\ManyToOne(targetEntity: Categorie::class)]
    #[ORM\JoinColumn(name: "categorieId", referencedColumnName: "categorieId", nullable: true)]
    private $categorie;

    // collaborateur 1 ---- * reference
    #[ORM\ManyToOne(targetEntity: Collaborateur::class)]
    #[ORM\JoinColumn(name: "collaborateurId", referencedColumnName: "collaborateurId", nullable: true)]
    private $collaborateur;

    /* =======================
     *  ONE-TO-MANY RELATIONS
     * ======================= */

    // reference 1 ---- * referenceDocument
    #[ORM\OneToMany(targetEntity: ReferenceDocuments::class, mappedBy: "reference")]
    private Collection $referenceDocuments;

    // reference 1 ---- * referenceCollaborateur
    #[ORM\OneToMany(targetEntity: ReferenceCollaborateur::class, mappedBy: "reference")]
    private Collection $referenceCollaborateurs;

    /* =======================
     *  MANY-TO-MANY RELATIONS
     * ======================= */

    // Reference * ---- * BailleurFond
    #[ORM\ManyToMany(targetEntity: BailleurFond::class, inversedBy: "references")]
    #[ORM\JoinTable(name: "reference_bailleurfond")]
    #[ORM\JoinColumn(name: "reference_id", referencedColumnName: "referenceID")]
    #[ORM\InverseJoinColumn(name: "bailleur_fond_id", referencedColumnName: "bailleurFondId")]
    private Collection $bailleurfonds;

    // reference * ---- * environnementDeveloppement
    #[ORM\ManyToMany(targetEntity: EnvironnementDeveloppement::class, inversedBy: "references")]
    #[ORM\JoinTable(name: "reference_environnement_developpement")]
    #[ORM\JoinColumn(name: "reference_id", referencedColumnName: "referenceID")]
    #[ORM\InverseJoinColumn(name: "environnement_developpement_id", referencedColumnName: "environnementDeveloppementId")]
    private Collection $environnementsDeveloppement;

    // reference * ---- * technologie
    #[ORM\ManyToMany(targetEntity: Technologie::class, inversedBy: "references")]
    #[ORM\JoinTable(name: "reference_technologie")]
    #[ORM\JoinColumn(name: "reference_id", referencedColumnName: "referenceID")]
    #[ORM\InverseJoinColumn(name: "technologie_id", referencedColumnName: "technologieId")]
    private Collection $technologies;

    // reference * ---- * methodologie
    #[ORM\ManyToMany(targetEntity: Methodologie::class, inversedBy: "references")]
    #[ORM\JoinTable(name: "reference_methodologie")]
    #[ORM\JoinColumn(name: "reference_id", referencedColumnName: "referenceID")]
    #[ORM\InverseJoinColumn(name: "methodologie_id", referencedColumnName: "methodologieId")]
    private Collection $methodologies;

    // reference * ---- * role
    #[ORM\ManyToMany(targetEntity: Role::class, inversedBy: "references")]
    #[ORM\JoinTable(name: "reference_role")]
    #[ORM\JoinColumn(name: "reference_id", referencedColumnName: "referenceID")]
    #[ORM\InverseJoinColumn(name: "role_id", referencedColumnName: "roleId")]
    private Collection $roles;

    // reference * ---- * appelOffres
    #[ORM\ManyToMany(targetEntity: AppelOffres::class, inversedBy: "references")]
    #[ORM\JoinTable(name: "reference_appel_offres")]
    #[ORM\JoinColumn(name: "reference_id", referencedColumnName: "referenceID")]
    #[ORM\InverseJoinColumn(name: "appel_offres_id", referencedColumnName: "appelOffresId")]
    private Collection $appelOffres;

    // reference * ---- * referenceCaracteristiqueSpeciale
    #[ORM\ManyToMany(targetEntity: ReferenceCaracteristiqueSpeciale::class, inversedBy: "references")]
    #[ORM\JoinTable(name: "reference_caracteristique_speciale")]
    #[ORM\JoinColumn(name: "reference_id", referencedColumnName: "referenceID")]
    #[ORM\InverseJoinColumn(name: "reference_caracteristique_speciale_id", referencedColumnName: "referenceCaracteristiqueSpecialeId")]
    private Collection $referenceCaracteristiqueSpeciales;

    public function __construct()
    {
        $this->referenceDocuments = new ArrayCollection();
        $this->referenceCollaborateurs = new ArrayCollection();
        $this->bailleurfonds = new ArrayCollection();
        $this->environnementsDeveloppement = new ArrayCollection();
        $this->technologies = new ArrayCollection();
        $this->methodologies = new ArrayCollection();
        $this->roles = new ArrayCollection();
        $this->appelOffres = new ArrayCollection();
        $this->referenceCaracteristiqueSpeciales = new ArrayCollection();
    }

    /* =======================
     *  GETTERS / SETTERS
     * ======================= */

    public function getReferenceID(): ?int
    {
        return $this->referenceID;
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

    public function getReferenceDureeExecution(): ?int
    {
        return $this->referenceDureeExecution;
    }

    public function setReferenceDureeExecution(?int $referenceDureeExecution): self
    {
        $this->referenceDureeExecution = $referenceDureeExecution;
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

    public function getReferenceDateReceptionProvisoire(): ?\DateTimeInterface
    {
        return $this->referenceDateReceptionProvisoire;
    }

    public function setReferenceDateReceptionProvisoire(?\DateTimeInterface $referenceDateReceptionProvisoire): self
    {
        $this->referenceDateReceptionProvisoire = $referenceDateReceptionProvisoire;
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

    public function getReferenceDescriptionServiceEffectivementRendus(): ?string
    {
        return $this->referenceDescriptionServiceEffectivementRendus;
    }

    public function setReferenceDescriptionServiceEffectivementRendus(?string $referenceDescriptionServiceEffectivementRendus): self
    {
        $this->referenceDescriptionServiceEffectivementRendus = $referenceDescriptionServiceEffectivementRendus;
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

    public function getReferencePartBudget(): ?string
    {
        return $this->referencePartBudget;
    }

    public function setReferencePartBudget(?string $referencePartBudget): self
    {
        $this->referencePartBudget = $referencePartBudget;
        return $this;
    }

    public function getReferenceRemarque(): ?string
    {
        return $this->referenceRemarque;
    }

    public function setReferenceRemarque(?string $referenceRemarque): self
    {
        $this->referenceRemarque = $referenceRemarque;
        return $this;
    }

    /* ======= ManyToOne getters/setters ======= */
    public function getPays()
    {
        return $this->pays;
    }

    public function setPays($Pays): self
    {
        $this->Pays = $Pays;
        return $this;
    }

    public function getLieu()
    {
        return $this->lieu;
    }

    public function setLieu($lieu): self
    {
        $this->lieu = $lieu;
        return $this;
    }

    public function getDevises()
    {
        return $this->devises;
    }

    public function setDevises($devises): self
    {
        $this->devises = $devises;
        return $this;
    }

    public function getCategorie()
    {
        return $this->categorie;
    }

    public function setCategorie($categorie): self
    {
        $this->categorie = $categorie;
        return $this;
    }

    public function getCollaborateur()
    {
        return $this->collaborateur;
    }

    public function setCollaborateur($collaborateur): self
    {
        $this->collaborateur = $collaborateur;
        return $this;
    }

    /* ======= OneToMany: ReferenceDocument ======= */

    /**
     * @return Collection<int, ReferenceDocument>
     */
    public function getReferenceDocuments(): Collection
    {
        return $this->referenceDocuments;
    }

    public function addReferenceDocument(ReferenceDocument $referenceDocument): self
    {
        if (!$this->referenceDocuments->contains($referenceDocument)) {
            $this->referenceDocuments[] = $referenceDocument;
            $referenceDocument->setReference($this);
        }

        return $this;
    }

    public function removeReferenceDocument(ReferenceDocument $referenceDocument): self
    {
        if ($this->referenceDocuments->removeElement($referenceDocument)) {
            if ($referenceDocument->getReference() === $this) {
                $referenceDocument->setReference(null);
            }
        }

        return $this;
    }

    /* ======= OneToMany: ReferenceCollaborateur ======= */

    /**
     * @return Collection<int, ReferenceCollaborateur>
     */
    public function getReferenceCollaborateurs(): Collection
    {
        return $this->referenceCollaborateurs;
    }

    public function addReferenceCollaborateur(ReferenceCollaborateur $referenceCollaborateur): self
    {
        if (!$this->referenceCollaborateurs->contains($referenceCollaborateur)) {
            $this->referenceCollaborateurs[] = $referenceCollaborateur;
            $referenceCollaborateur->setReference($this);
        }

        return $this;
    }

    public function removeReferenceCollaborateur(ReferenceCollaborateur $referenceCollaborateur): self
    {
        if ($this->referenceCollaborateurs->removeElement($referenceCollaborateur)) {
            if ($referenceCollaborateur->getReference() === $this) {
                $referenceCollaborateur->setReference(null);
            }
        }

        return $this;
    }

    /* ======= ManyToMany: BailleurFond (bailleurfonds) ======= */

    /**
     * @return Collection<int, BailleurFond>
     */
    public function getBailleurfonds(): Collection
    {
        return $this->bailleurfonds;
    }

    public function addBailleurfond(BailleurFond $bailleurFond): self
    {
        if (!$this->bailleurfonds->contains($bailleurFond)) {
            $this->bailleurfonds[] = $bailleurFond;
        }

        return $this;
    }

    public function removeBailleurfond(BailleurFond $bailleurFond): self
    {
        $this->bailleurfonds->removeElement($bailleurFond);
        return $this;
    }

    /* ======= ManyToMany: EnvironnementDeveloppement ======= */

    /**
     * @return Collection<int, EnvironnementDeveloppement>
     */
    public function getEnvironnementsDeveloppement(): Collection
    {
        return $this->environnementsDeveloppement;
    }

    public function addEnvironnementDeveloppement(EnvironnementDeveloppement $env): self
    {
        if (!$this->environnementsDeveloppement->contains($env)) {
            $this->environnementsDeveloppement[] = $env;
        }

        return $this;
    }

    public function removeEnvironnementDeveloppement(EnvironnementDeveloppement $env): self
    {
        $this->environnementsDeveloppement->removeElement($env);
        return $this;
    }

    /* ======= ManyToMany: Technologie ======= */

    /**
     * @return Collection<int, Technologie>
     */
    public function getTechnologies(): Collection
    {
        return $this->technologies;
    }

    public function addTechnologie(Technologie $technologie): self
    {
        if (!$this->technologies->contains($technologie)) {
            $this->technologies[] = $technologie;
        }

        return $this;
    }

    public function removeTechnologie(Technologie $technologie): self
    {
        $this->technologies->removeElement($technologie);
        return $this;
    }

    /* ======= ManyToMany: Methodologie ======= */

    /**
     * @return Collection<int, Methodologie>
     */
    public function getMethodologies(): Collection
    {
        return $this->methodologies;
    }

    public function addMethodologie(Methodologie $methodologie): self
    {
        if (!$this->methodologies->contains($methodologie)) {
            $this->methodologies[] = $methodologie;
        }

        return $this;
    }

    public function removeMethodologie(Methodologie $methodologie): self
    {
        $this->methodologies->removeElement($methodologie);
        return $this;
    }

    /* ======= ManyToMany: Role ======= */

    /**
     * @return Collection<int, Role>
     */
    public function getRolesReference(): Collection
    {
        return $this->roles;
    }

    public function addRoleReference(Role $role): self
    {
        if (!$this->roles->contains($role)) {
            $this->roles[] = $role;
        }

        return $this;
    }

    public function removeRoleReference(Role $role): self
    {
        $this->roles->removeElement($role);
        return $this;
    }

    /* ======= ManyToMany: AppelOffres ======= */

    /**
     * @return Collection<int, AppelOffres>
     */
    public function getAppelOffres(): Collection
    {
        return $this->appelOffres;
    }

    public function addAppelOffres(AppelOffres $appelOffres): self
    {
        if (!$this->appelOffres->contains($appelOffres)) {
            $this->appelOffres[] = $appelOffres;
        }

        return $this;
    }

    public function removeAppelOffres(AppelOffres $appelOffres): self
    {
        $this->appelOffres->removeElement($appelOffres);
        return $this;
    }

    /* ======= ManyToMany: ReferenceCaracteristiqueSpeciale ======= */

    /**
     * @return Collection<int, ReferenceCaracteristiqueSpeciale>
     */
    public function getReferenceCaracteristiqueSpeciales(): Collection
    {
        return $this->referenceCaracteristiqueSpeciales;
    }

    public function addReferenceCaracteristiqueSpeciale(ReferenceCaracteristiqueSpeciale $carac): self
    {
        if (!$this->referenceCaracteristiqueSpeciales->contains($carac)) {
            $this->referenceCaracteristiqueSpeciales[] = $carac;
        }

        return $this;
    }

    public function removeReferenceCaracteristiqueSpeciale(ReferenceCaracteristiqueSpeciale $carac): self
    {
        $this->referenceCaracteristiqueSpeciales->removeElement($carac);
        return $this;
    }
}
