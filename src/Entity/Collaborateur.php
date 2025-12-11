<?php

namespace App\Entity;

use App\Repository\CollaborateurRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\Entity(repositoryClass: CollaborateurRepository::class)]
#[ORM\Table(name: "collaborateur")]
class Collaborateur
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: "collaborateurId")]
    private ?int $collaborateurId = null;

    #[ORM\Column(name: "collaborateurPrenom", length: 254, nullable: true)]
    private ?string $collaborateurPrenom = null;

    #[ORM\Column(name: "collaborateurNom", length: 254, nullable: true)]
    private ?string $collaborateurNom = null;

    #[ORM\Column(name: "collaborateurDateNaissance", type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $collaborateurDateNaissance = null;

    #[ORM\Column(name: "collaborateurLieuNaissance", length: 254, nullable: true)]
    private ?string $collaborateurLieuNaissance = null;

    #[ORM\Column(name: "collaborateurAdresse", length: 254, nullable: true)]
    private ?string $collaborateurAdresse = null;

    #[ORM\Column(name: "collaborateurEmail1", length: 254, nullable: true)]
    private ?string $collaborateurEmail1 = null;

    #[ORM\Column(name: "collaborateurEmail2", length: 254, nullable: true)]
    private ?string $collaborateurEmail2 = null;

    #[ORM\Column(name: "collaborateurTelephone1", length: 254, nullable: true)]
    private ?string $collaborateurTelephone1 = null;

    #[ORM\Column(name: "collaborateurTelephone2", length: 254, nullable: true)]
    private ?string $collaborateurTelephone2 = null;

    #[ORM\Column(name: "collaborateurCV", length: 254, nullable: true)]
    private ?string $collaborateurCV = null;

    #[ORM\ManyToOne(targetEntity: Pays::class)]
    #[ORM\JoinColumn(name: "paysId", referencedColumnName: "paysId", nullable: true, onDelete: "SET NULL")]
    private ?Pays $pays = null;

    #[ORM\ManyToOne(targetEntity: Nationalite::class)]
    #[ORM\JoinColumn(name: "nationaliteId", referencedColumnName: "id", nullable: true, onDelete: "SET NULL")]
    private ?Nationalite $nationalite = null;

    #[ORM\ManyToOne(targetEntity: AppelOffresPersonnelCle::class)]
    #[ORM\JoinColumn(name: "appelOffresPersonnelCleId", referencedColumnName: "appelOffresPersonnelCleId", nullable: true, onDelete: "SET NULL")]
    private ?AppelOffresPersonnelCle $appelOffresPersonnelCle = null;

    #[ORM\OneToMany(targetEntity: EmployeExperience::class, mappedBy: 'collaborateur', cascade: ["persist","remove"])]
    private Collection $experiences;

    #[ORM\ManyToMany(targetEntity: CollaborateurEducation::class, inversedBy: 'collaborateurs')]
    #[ORM\JoinTable(name: 'collaborateur_collaborateur_education')]
    #[ORM\JoinColumn(name: 'collaborateurId', referencedColumnName: 'collaborateurId')]
    #[ORM\InverseJoinColumn(name: 'collaborateurEducationId', referencedColumnName: 'collaborateurEducationId')]
    private Collection $educations;

    #[ORM\OneToMany(targetEntity: CollaborateurDocuments::class, mappedBy: 'collaborateur', cascade: ["persist","remove"])]
    private Collection $collaborateurDocuments; 

    public function __construct()
    {
        $this->experiences = new ArrayCollection();
        $this->educations = new ArrayCollection();
        $this->collaborateurDocuments = new ArrayCollection();
    }

    public function getCollaborateurId(): ?int
    {
        return $this->collaborateurId;
    }

    public function getCollaborateurPrenom(): ?string
    {
        return $this->collaborateurPrenom;
    }

    public function setCollaborateurPrenom(?string $collaborateurPrenom): static
    {
        $this->collaborateurPrenom = $collaborateurPrenom;
        return $this;
    }

    public function getCollaborateurNom(): ?string
    {
        return $this->collaborateurNom;
    }

    public function setCollaborateurNom(?string $collaborateurNom): static
    {
        $this->collaborateurNom = $collaborateurNom;
        return $this;
    }

    public function getCollaborateurDateNaissance(): ?\DateTimeInterface
    {
        return $this->collaborateurDateNaissance;
    }

    public function setCollaborateurDateNaissance(?\DateTimeInterface $collaborateurDateNaissance): static
    {
        $this->collaborateurDateNaissance = $collaborateurDateNaissance;
        return $this;
    }

    public function getCollaborateurLieuNaissance(): ?string
    {
        return $this->collaborateurLieuNaissance;
    }

    public function setCollaborateurLieuNaissance(?string $collaborateurLieuNaissance): static
    {
        $this->collaborateurLieuNaissance = $collaborateurLieuNaissance;
        return $this;
    }

    public function getCollaborateurAdresse(): ?string
    {
        return $this->collaborateurAdresse;
    }

    public function setCollaborateurAdresse(?string $collaborateurAdresse): static
    {
        $this->collaborateurAdresse = $collaborateurAdresse;
        return $this;
    }

    public function getCollaborateurEmail1(): ?string
    {
        return $this->collaborateurEmail1;
    }

    public function setCollaborateurEmail1(?string $collaborateurEmail1): static
    {
        $this->collaborateurEmail1 = $collaborateurEmail1;
        return $this;
    }

    public function getCollaborateurEmail2(): ?string
    {
        return $this->collaborateurEmail2;
    }

    public function setCollaborateurEmail2(?string $collaborateurEmail2): static
    {
        $this->collaborateurEmail2 = $collaborateurEmail2;
        return $this;
    }

    public function getCollaborateurTelephone1(): ?string
    {
        return $this->collaborateurTelephone1;
    }

    public function setCollaborateurTelephone1(?string $collaborateurTelephone1): static
    {
        $this->collaborateurTelephone1 = $collaborateurTelephone1;
        return $this;
    }

    public function getCollaborateurTelephone2(): ?string
    {
        return $this->collaborateurTelephone2;
    }

    public function setCollaborateurTelephone2(?string $collaborateurTelephone2): static
    {
        $this->collaborateurTelephone2 = $collaborateurTelephone2;
        return $this;
    }

    public function getCollaborateurCV(): ?string
    {
        return $this->collaborateurCV;
    }

    public function setCollaborateurCV(?string $collaborateurCV): static
    {
        $this->collaborateurCV = $collaborateurCV;
        return $this;
    }

    public function getPays(): ?Pays
    {
        return $this->pays;
    }

    public function setPays(?Pays $pays): static
    {
        $this->pays = $pays;
        return $this;
    }

    public function getNationalite(): ?Nationalite
    {
        return $this->nationalite;
    }

    public function setNationalite(?Nationalite $nationalite): static
    {
        $this->nationalite = $nationalite;
        return $this;
    }

    public function getAppelOffresPersonnelCle(): ?AppelOffresPersonnelCle
    {
        return $this->appelOffresPersonnelCle;
    }

    public function setAppelOffresPersonnelCle(?AppelOffresPersonnelCle $appelOffresPersonnelCle): static
    {
        $this->appelOffresPersonnelCle = $appelOffresPersonnelCle;
        return $this;
    }

    public function getExperiences(): Collection
    {
        return $this->experiences;
    }

    public function addExperience(EmployeExperience $experience): static
    {
        if (!$this->experiences->contains($experience)) {
            $this->experiences->add($experience);
            $experience->setCollaborateur($this);
        }
        return $this;
    }

    public function removeExperience(EmployeExperience $experience): static
    {
        if ($this->experiences->removeElement($experience)) {
            if ($experience->getCollaborateur() === $this) {
                $experience->setCollaborateur(null);
            }
        }
        return $this;
    }

    public function getEducations(): Collection
    {
        return $this->educations;
    }

    public function addEducation(CollaborateurEducation $education): static
    {
        if (!$this->educations->contains($education)) {
            $this->educations->add($education);
            $education->addCollaborateur($this);
        }
        return $this;
    }

    public function removeEducation(CollaborateurEducation $education): static
    {
        if ($this->educations->removeElement($education)) {
            $education->removeCollaborateur($this);
        }
        return $this;
    }

    public function getCollaborateurDocuments(): Collection
    {
        return $this->collaborateurDocuments;
    }

 
    




}