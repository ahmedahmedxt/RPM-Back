<?php

namespace App\Entity;

use App\Repository\ReferenceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReferenceRepository::class)]
#[ORM\Table(name: "reference")]
#[ORM\UniqueConstraint(name: "uniq_reference_ref", columns: ["referenceRef"])]
#[ORM\UniqueConstraint(name: "uniq_ref_categorie_ordre", columns: ["categorieId", "referenceOrdre"])]
class Reference
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer", name: "referenceID")]
    private ?int $referenceID = null;

    #[ORM\Column(type: "string", length: 254, nullable: true, name: "referenceRef")]
    private ?string $referenceRef = null;

    #[ORM\Column(type: "integer", nullable: false, name: "referenceOrdre", options: ["default" => 0])]
    private ?int $referenceOrdre = 0;

    #[ORM\Column(type: "string", length: 254, nullable: true, name: "referenceTitre")]
    private ?string $referenceTitre = null;

    #[ORM\Column(type: "string", length: 254, nullable: true, name: "referenceLibelle")]
    private ?string $referenceLibelle = null;

    #[ORM\Column(type: "string", length: 254, nullable: true, name: "referenceUrlFonctionnel")]
    private ?string $referenceUrlFonctionnel = null;

    #[ORM\Column(type: "integer", nullable: true, name: "referenceDureeExecution")]
    private ?int $referenceDureeExecution = null;

    #[ORM\Column(type: "date", nullable: true, name: "referenceDateDemarrage")]
    private ?\DateTimeInterface $referenceDateDemarrage = null;

    #[ORM\Column(type: "date", nullable: true, name: "referenceDateAchevement")]
    private ?\DateTimeInterface $referenceDateAchevement = null;

    #[ORM\Column(type: "date", nullable: true, name: "referenceDateReceptionProvisoire")]
    private ?\DateTimeInterface $referenceDateReceptionProvisoire = null;

    #[ORM\Column(type: "integer", nullable: true, name: "referenceDureeGarantie")]
    private ?int $referenceDureeGarantie = null;

    #[ORM\Column(type: "date", nullable: true, name: "referenceDateReceptionDefinitive")]
    private ?\DateTimeInterface $referenceDateReceptionDefinitive = null;

    #[ORM\Column(type: "text", nullable: true, name: "referenceCaracteristiques")]
    private ?string $referenceCaracteristiques = null;

    #[ORM\Column(type: "text", nullable: true, name: "referenceDescription")]
    private ?string $referenceDescription = null;

    #[ORM\Column(type: "text", nullable: true, name: "referenceDescriptionServiceEffectivementRendus")]
    private ?string $referenceDescriptionServiceEffectivementRendus = null;

    #[ORM\Column(type: "float", nullable: true, name: "referenceBudget")]
    private ?float $referenceBudget = null;

    #[ORM\Column(type: "string", length: 254, nullable: true, name: "referencePartBudget")]
    private ?string $referencePartBudget = null;

    #[ORM\Column(type: "text", nullable: true, name: "referenceRemarque")]
    private ?string $referenceRemarque = null;

    #[ORM\ManyToOne(targetEntity: Pays::class)]
    #[ORM\JoinColumn(name: "paysId", referencedColumnName: "paysId", nullable: true)]
    private ?Pays $pays = null;

    #[ORM\ManyToOne(targetEntity: Lieu::class)]
    #[ORM\JoinColumn(name: "lieuId", referencedColumnName: "lieuId", nullable: true)]
    private ?Lieu $lieu = null;

    #[ORM\ManyToOne(targetEntity: Devises::class)]
    #[ORM\JoinColumn(name: "devisesId", referencedColumnName: "devisesId", nullable: true)]
    private ?Devises $devises = null;

    #[ORM\ManyToOne(targetEntity: Categorie::class)]
    #[ORM\JoinColumn(name: "categorieId", referencedColumnName: "categorieId", nullable: true)]
    private ?Categorie $categorie = null;

    #[ORM\OneToMany(
        targetEntity: ReferenceDocuments::class,
        mappedBy: "reference",
        cascade: ["persist", "remove"],
        orphanRemoval: true
    )]
    private Collection $referenceDocuments;

    #[ORM\OneToMany(
        targetEntity: ReferenceCollaborateur::class,
        mappedBy: "reference",
        cascade: ["persist", "remove"],
        orphanRemoval: true
    )]
    private Collection $referenceCollaborateurs;

    #[ORM\ManyToMany(targetEntity: BailleurFond::class, inversedBy: "references")]
    #[ORM\JoinTable(name: "reference_bailleurfond")]
    #[ORM\JoinColumn(name: "reference_id", referencedColumnName: "referenceID")]
    #[ORM\InverseJoinColumn(name: "bailleur_fond_id", referencedColumnName: "bailleurFondId")]
    private Collection $bailleurfonds;

    #[ORM\ManyToMany(targetEntity: EnvironnementDeveloppement::class, inversedBy: "references")]
    #[ORM\JoinTable(name: "reference_environnement_developpement")]
    #[ORM\JoinColumn(name: "reference_id", referencedColumnName: "referenceID")]
    #[ORM\InverseJoinColumn(name: "environnement_developpement_id", referencedColumnName: "environnementDeveloppementId")]
    private Collection $environnementsDeveloppement;

    #[ORM\ManyToMany(targetEntity: Technologie::class, inversedBy: "references")]
    #[ORM\JoinTable(name: "reference_technologie")]
    #[ORM\JoinColumn(name: "reference_id", referencedColumnName: "referenceID")]
    #[ORM\InverseJoinColumn(name: "technologie_id", referencedColumnName: "technologieId")]
    private Collection $technologies;

    #[ORM\ManyToMany(targetEntity: Methodologie::class, inversedBy: "references")]
    #[ORM\JoinTable(name: "reference_methodologie")]
    #[ORM\JoinColumn(name: "reference_id", referencedColumnName: "referenceID")]
    #[ORM\InverseJoinColumn(name: "methodologie_id", referencedColumnName: "methodologieId")]
    private Collection $methodologies;

    #[ORM\ManyToMany(targetEntity: Role::class, inversedBy: "references")]
    #[ORM\JoinTable(name: "reference_role")]
    #[ORM\JoinColumn(name: "reference_id", referencedColumnName: "referenceID")]
    #[ORM\InverseJoinColumn(name: "role_id", referencedColumnName: "roleId")]
    private Collection $roles;

    #[ORM\ManyToMany(targetEntity: AppelOffres::class, inversedBy: "references")]
    #[ORM\JoinTable(name: "reference_appel_offres")]
    #[ORM\JoinColumn(name: "reference_id", referencedColumnName: "referenceID")]
    #[ORM\InverseJoinColumn(name: "appel_offres_id", referencedColumnName: "appelOffresId")]
    private Collection $appelOffres;

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

    public function getReferenceID(): ?int { return $this->referenceID; }

    public function getReferenceRef(): ?string { return $this->referenceRef; }
    public function setReferenceRef(?string $referenceRef): self { $this->referenceRef = $referenceRef; return $this; }

    public function getReferenceOrdre(): ?int { return $this->referenceOrdre; }

    public function setReferenceOrdre(?int $referenceOrdre): self
    {
        $this->referenceOrdre = $referenceOrdre ?? 0;
        $this->rebuildReferenceRefFromCategorieShort($this->categorie?->getCategorieShort());
        return $this;
    }

    public function getReferenceTitre(): ?string { return $this->referenceTitre; }
    public function setReferenceTitre(?string $referenceTitre): self { $this->referenceTitre = $referenceTitre; return $this; }

    public function getReferenceLibelle(): ?string { return $this->referenceLibelle; }
    public function setReferenceLibelle(?string $referenceLibelle): self { $this->referenceLibelle = $referenceLibelle; return $this; }

    public function getReferenceUrlFonctionnel(): ?string { return $this->referenceUrlFonctionnel; }
    public function setReferenceUrlFonctionnel(?string $referenceUrlFonctionnel): self { $this->referenceUrlFonctionnel = $referenceUrlFonctionnel; return $this; }

    public function getReferenceDureeExecution(): ?int { return $this->referenceDureeExecution; }
    public function setReferenceDureeExecution(?int $referenceDureeExecution): self { $this->referenceDureeExecution = $referenceDureeExecution; return $this; }

    public function getReferenceDateDemarrage(): ?\DateTimeInterface { return $this->referenceDateDemarrage; }
    public function setReferenceDateDemarrage(?\DateTimeInterface $d): self { $this->referenceDateDemarrage = $d; return $this; }

    public function getReferenceDateAchevement(): ?\DateTimeInterface { return $this->referenceDateAchevement; }
    public function setReferenceDateAchevement(?\DateTimeInterface $d): self { $this->referenceDateAchevement = $d; return $this; }

    public function getReferenceDateReceptionProvisoire(): ?\DateTimeInterface { return $this->referenceDateReceptionProvisoire; }
    public function setReferenceDateReceptionProvisoire(?\DateTimeInterface $d): self { $this->referenceDateReceptionProvisoire = $d; return $this; }

    public function getReferenceDureeGarantie(): ?int { return $this->referenceDureeGarantie; }
    public function setReferenceDureeGarantie(?int $d): self { $this->referenceDureeGarantie = $d; return $this; }

    public function getReferenceDateReceptionDefinitive(): ?\DateTimeInterface { return $this->referenceDateReceptionDefinitive; }
    public function setReferenceDateReceptionDefinitive(?\DateTimeInterface $d): self { $this->referenceDateReceptionDefinitive = $d; return $this; }

    public function getReferenceCaracteristiques(): ?string { return $this->referenceCaracteristiques; }
    public function setReferenceCaracteristiques(?string $v): self { $this->referenceCaracteristiques = $v; return $this; }

    public function getReferenceDescription(): ?string { return $this->referenceDescription; }
    public function setReferenceDescription(?string $v): self { $this->referenceDescription = $v; return $this; }

    public function getReferenceDescriptionServiceEffectivementRendus(): ?string { return $this->referenceDescriptionServiceEffectivementRendus; }
    public function setReferenceDescriptionServiceEffectivementRendus(?string $v): self { $this->referenceDescriptionServiceEffectivementRendus = $v; return $this; }

    public function getReferenceBudget(): ?float { return $this->referenceBudget; }
    public function setReferenceBudget(?float $v): self { $this->referenceBudget = $v; return $this; }

    public function getReferencePartBudget(): ?string { return $this->referencePartBudget; }
    public function setReferencePartBudget(?string $v): self { $this->referencePartBudget = $v; return $this; }

    public function getReferenceRemarque(): ?string { return $this->referenceRemarque; }
    public function setReferenceRemarque(?string $v): self { $this->referenceRemarque = $v; return $this; }

    public function getPays(): ?Pays { return $this->pays; }
    public function setPays(?Pays $pays): self { $this->pays = $pays; return $this; }

    public function getLieu(): ?Lieu { return $this->lieu; }
    public function setLieu(?Lieu $lieu): self { $this->lieu = $lieu; return $this; }

    public function getDevises(): ?Devises { return $this->devises; }
    public function setDevises(?Devises $devises): self { $this->devises = $devises; return $this; }

    public function getCategorie(): ?Categorie { return $this->categorie; }

    public function setCategorie(?Categorie $categorie): self
    {
        $this->categorie = $categorie;
        $this->rebuildReferenceRefFromCategorieShort($this->categorie?->getCategorieShort());
        return $this;
    }

    public function getReferenceDocuments(): Collection { return $this->referenceDocuments; }
    public function getReferenceCollaborateurs(): Collection { return $this->referenceCollaborateurs; }

    public function getBailleurfonds(): Collection { return $this->bailleurfonds; }

    public function getEnvironnementsDeveloppement(): Collection { return $this->environnementsDeveloppement; }

    public function getTechnologies(): Collection { return $this->technologies; }

    public function getMethodologies(): Collection { return $this->methodologies; }

    public function getRolesReference(): Collection { return $this->roles; }

    public function getReferenceCaracteristiqueSpeciales(): Collection { return $this->referenceCaracteristiqueSpeciales; }


    public function addBailleurfond(BailleurFond $b): self
    {
        if (!$this->bailleurfonds->contains($b)) {
            $this->bailleurfonds->add($b);
        }
        return $this;
    }

    public function removeBailleurfond(BailleurFond $b): self
    {
        $this->bailleurfonds->removeElement($b);
        return $this;
    }

    public function addEnvironnementDeveloppement(EnvironnementDeveloppement $env): self
    {
        if (!$this->environnementsDeveloppement->contains($env)) {
            $this->environnementsDeveloppement->add($env);
        }
        return $this;
    }

    public function removeEnvironnementDeveloppement(EnvironnementDeveloppement $env): self
    {
        $this->environnementsDeveloppement->removeElement($env);
        return $this;
    }
    public function addTechnologie(Technologie $t): self
    {
        if (!$this->technologies->contains($t)) {
            $this->technologies->add($t);
        }
        return $this;
    }

    public function removeTechnologie(Technologie $t): self
    {
        $this->technologies->removeElement($t);
        return $this;
    }
    public function addMethodologie(Methodologie $m): self
    {
        if (!$this->methodologies->contains($m)) {
            $this->methodologies->add($m);
        }
        return $this;
    }

    public function removeMethodologie(Methodologie $m): self
    {
        $this->methodologies->removeElement($m);
        return $this;
    }
    public function addRoleReference(Role $r): self
    {
        if (!$this->roles->contains($r)) {
            $this->roles->add($r);
        }
        return $this;
    }

    public function removeRoleReference(Role $r): self
    {
        $this->roles->removeElement($r);
        return $this;
    }
    public function getAppelOffres(): Collection { return $this->appelOffres; }
    public function addAppelOffres(AppelOffres $appelOffre): self
    {
        if (!$this->appelOffres->contains($appelOffre)) {
            $this->appelOffres->add($appelOffre);

            if (method_exists($appelOffre, 'addReference')) {
                $appelOffre->addReference($this);
            }
        }

        return $this;
    }

    public function removeAppelOffres(AppelOffres $appelOffre): self
    {
        if ($this->appelOffres->removeElement($appelOffre)) {
            if (method_exists($appelOffre, 'removeReference')) {
                $appelOffre->removeReference($this);
            }
        }

        return $this;
    }
    public function addReferenceCaracteristiqueSpeciale(ReferenceCaracteristiqueSpeciale $cs): self
    {
        if (!$this->referenceCaracteristiqueSpeciales->contains($cs)) {
            $this->referenceCaracteristiqueSpeciales->add($cs);
        }
        return $this;
    }

    public function removeReferenceCaracteristiqueSpeciale(ReferenceCaracteristiqueSpeciale $cs): self
    {
        $this->referenceCaracteristiqueSpeciales->removeElement($cs);
        return $this;
    }

    public function rebuildReferenceRefFromCategorieShort(?string $categorieShort): self
    {
        if (!$categorieShort) return $this;

        $ordre = (int) ($this->referenceOrdre ?? 0);
        if ($ordre < 1) return $this;

        $this->referenceRef =
            'REF.' .
            strtoupper(trim($categorieShort)) .
            ' ' .
            str_pad((string)$ordre, 3, '0', STR_PAD_LEFT);

        return $this;
    }

    public function addReferenceDocument(ReferenceDocuments $doc): self
    {
        if (!$this->referenceDocuments->contains($doc)) {
            $this->referenceDocuments->add($doc);
            $doc->setReference($this);
        }
        return $this;
    }

    public function removeReferenceDocument(ReferenceDocuments $doc): self
    {
        if ($this->referenceDocuments->removeElement($doc)) {
            if ($doc->getReference() === $this) {
                $doc->setReference(null);
            }
        }
        return $this;
    }

}