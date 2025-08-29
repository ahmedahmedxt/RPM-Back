<?php

namespace App\Entity;

use App\Repository\EmployeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EmployeRepository::class)]
class Employe
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: "employeId")]
    private ?int $employeId = null;

    #[ORM\Column(name: "employeNom", length: 254, nullable: true)]
    private ?string $employeNom = null;

    #[ORM\Column(name: "employePrenom", length: 254, nullable: true)]
    private ?string $employePrenom = null;

    #[ORM\Column(name : "employeDateNaissance", type: Types::DATE_MUTABLE)]
    #[Assert\NotBlank]
    private ?\DateTimeInterface $employeDateNaissance = null;

    #[ORM\Column(name: "employeLieuNaissance", length: 254)]
    private ?string $employeLieuNaissance = null;

    #[ORM\Column(name : "employeAdresse",length: 254)]
    #[Assert\NotBlank]
    private ?string $employeAdresse = null;

    #[ORM\Column(name: "employePrincipaleQualification", length: 254)]
    #[Assert\NotBlank]
    private ?string $employePrincipaleQualification = null;

    #[ORM\Column(name : "employeFormationAutre", length: 254)]
    #[Assert\NotBlank]
    private ?string $employeFormationAutre = null;

    #[ORM\Column(name : "employeAffiliationDesAssociationGroupePro", length: 254)]
    #[Assert\NotBlank]
    private ?string $employeAffiliationDesAssociationsGroupPro = null;

    #[ORM\Column(name: "employeRemarque", length: 254, nullable: true)]
    private ?string $employeRemarque = null;

    #[ORM\ManyToOne(targetEntity: SituationFamiliale::class)]
    #[ORM\JoinColumn(name: "situationFamilialeId", referencedColumnName: "situationFamilialeId")]
    #[Assert\NotBlank]
    private ?SituationFamiliale $situationFamiliale;

    #[ORM\OneToMany(targetEntity: EmployeExperience::class, mappedBy: 'employe',cascade: ["persist","remove"])]
    private Collection $experiences;

    #[ORM\OneToMany(targetEntity: EmployeEducation::class, mappedBy: 'employe',cascade: ["persist","remove"])]
    private Collection $educations;

    #[ORM\OneToMany(targetEntity: EmployeDocuments::class, mappedBy: 'employeId')]
    private Collection $employeDocuments;

    #[ORM\ManyToOne(inversedBy: 'employes')]
    #[ORM\JoinColumn(name: "employePosteId", referencedColumnName: "employePosteId")]
    private ?EmployePoste $employePoste = null;

    #[ORM\ManyToOne(inversedBy: 'employes')]
    #[ORM\JoinColumn(name: "employeLangueId", referencedColumnName: "employeLangueId")]
    private ?EmployeLangue $employeLangue = null;


    public function __construct()
    {
        $this->experiences = new ArrayCollection();
        $this->educations = new ArrayCollection();
        $this->employeDocuments = new ArrayCollection();
    }

    public function getEmployeId(): ?int
    {
        return $this->employeId;
    }
    public function getEmployeDateNaissance(): ?\DateTimeInterface
    {
        return $this->employeDateNaissance;
    }

    public function setEmployeDateNaissance(\DateTimeInterface $employeDateNaissance): static
    {
        $this->employeDateNaissance = $employeDateNaissance;

        return $this;
    }

    public function getEmployeAdresse(): ?string
    {
        return $this->employeAdresse;
    }

    public function setEmployeAdresse(?string $employeAdresse): static
    {
        $this->employeAdresse = $employeAdresse;

        return $this;
    }

    public function getEmployePrincipaleQualification(): ?string
    {
        return $this->employePrincipaleQualification;
    }

    public function setEmployePrincipaleQualification(?string $employePrincipaleQualification): static
    {
        $this->employePrincipaleQualification = $employePrincipaleQualification;

        return $this;
    }

    public function getEmployeFormationAutre(): ?string
    {
        return $this->employeFormationAutre;
    }

    public function setEmployeFormationAutre(?string $employeFormation): static
    {
        $this->employeFormationAutre = $employeFormation;

        return $this;
    }

    public function getEmployeAffiliationDesAssociationsGroupPro(): ?string
    {
        return $this->employeAffiliationDesAssociationsGroupPro;
    }

    public function setEmployeAffiliationDesAssociationsGroupPro(?string $employeAffiliationDesAssociationsGroupPro): static
    {
        $this->employeAffiliationDesAssociationsGroupPro = $employeAffiliationDesAssociationsGroupPro;

        return $this;
    }

    public function getSituationFamiliale(): ?SituationFamiliale
    {
        return $this->situationFamiliale;
    }

    public function setSituationFamiliale(?SituationFamiliale $situationFamiliale): self
    {
        $this->situationFamiliale = $situationFamiliale;

        return $this;
    }
    /**
     * @return Collection|EmployeExperience[]
     */
    public function getExperiences(): Collection
    {
        return $this->experiences;
    }

    public function addExperience(EmployeExperience $experience): self
    {
        if (!$this->experiences->contains($experience)) {
            $this->experiences[] = $experience;
            $experience->setEmploye($this);
        }

        return $this;
    }

    public function removeExperience(EmployeExperience $experience): self
    {
        if ($this->experiences->removeElement($experience)) {
            // set the owning side to null (unless already changed)
            if ($experience->getEmploye() === $this) {
                $experience->setEmploye(null);
            }
        }

        return $this;
    }
     /**
     * @return Collection|EmployeEducation[]
     */
    public function getEducations(): Collection
    {
        return $this->educations;
    }

    public function addEducation(EmployeEducation $education): self
    {
        if (!$this->educations->contains($education)) {
            $this->educations[] = $education;
            $education->setEmploye($this);
        }

        return $this;
    }

    public function removeEducation(EmployeEducation $education): self
    {
        if ($this->educations->removeElement($education)) {
            // set the owning side to null (unless already changed)
            if ($education->getEmploye() === $this) {
                $education->setEmploye(null);
            }
        }

        return $this;
    }


    /**
     * @return Collection<int, EmployeDocuments>
     */
    public function getEmployeDocuments(): Collection
    {
        return $this->employeDocuments;
    }

    public function addEmployeDocument(EmployeDocuments $employeDocument): static
    {
        if (!$this->employeDocuments->contains($employeDocument)) {
            $this->employeDocuments->add($employeDocument);
            $employeDocument->setEmploye($this);
        }

        return $this;
    }

    public function removeEmployeDocument(EmployeDocuments $employeDocument): static
    {
        if ($this->employeDocuments->removeElement($employeDocument)) {
            // set the owning side to null (unless already changed)
            if ($employeDocument->getEmploye() === $this) {
                $employeDocument->setEmploye(null);
            }
        }

        return $this;
    }

    public function getEmployeNom(): ?string
    {
        return $this->employeNom;
    }

    public function setEmployeNom(?string $employeNom): static
    {
        $this->employeNom = $employeNom;

        return $this;
    }

    public function getEmployePrenom(): ?string
    {
        return $this->employePrenom;
    }

    public function setEmployePrenom(?string $employePrenom): static
    {
        $this->employePrenom = $employePrenom;

        return $this;
    }

    public function getEmployeRemarque(): ?string
    {
        return $this->employeRemarque;
    }

    public function setEmployeRemarque(?string $employeRemarque): static
    {
        $this->employeRemarque = $employeRemarque;

        return $this;
    }

    public function getEmployeLieuNaissance(): ?string
    {
        return $this->employeLieuNaissance;
    }

    public function setEmployeLieuNaissance(string $employeLieuNaissance): static
    {
        $this->employeLieuNaissance = $employeLieuNaissance;

        return $this;
    }

    public function getEmployePoste(): ?EmployePoste
    {
        return $this->employePoste;
    }

    public function setEmployePoste(?EmployePoste $employePoste): static
    {
        $this->employePoste = $employePoste;

        return $this;
    }

    public function getEmployeLangue(): ?EmployeLangue
    {
        return $this->employeLangue;
    }

    public function setEmployeLangue(?EmployeLangue $employeLangue): static
    {
        $this->employeLangue = $employeLangue;

        return $this;
    }
}
