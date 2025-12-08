<?php

namespace App\Entity;

use App\Repository\CollaborateurEducationRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\Entity(repositoryClass: CollaborateurEducationRepository::class)]
#[ORM\Table(name: "collaborateureducation")]
class CollaborateurEducation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: "collaborateurEducationId")]
    private ?int $collaborateurEducationId = null;

    #[ORM\Column(name: "collaborateurEducationNatureEtudes", length: 254, nullable: true)]
    private ?string $collaborateurEducationNatureEtudes = null;

    #[ORM\Column(name: "collaborateurEducationEtablissement", length: 254, nullable: true)]
    private ?string $collaborateurEducationEtablissement = null;

    #[ORM\Column(name: "collaborateurEducationAnneeObtention", type: "integer", nullable: true)]
    private ?int $collaborateurEducationAnneeObtention = null;

    #[ORM\ManyToOne(targetEntity: TypeDiplome::class, inversedBy: 'collaborateurEducation')]
    #[ORM\JoinColumn(name: "typeDiplomeId", referencedColumnName: "typeDiplomeId", nullable: false)]
    private ?TypeDiplome $typeDiplome = null;

    #[ORM\ManyToMany(targetEntity: Collaborateur::class, mappedBy: 'educations')]
    private Collection $collaborateurs;

    public function __construct()
    {
        $this->collaborateurs = new ArrayCollection();
    }

    public function getCollaborateurEducationId(): ?int
    {
        return $this->collaborateurEducationId;
    }

    public function getCollaborateurEducationNatureEtudes(): ?string
    {
        return $this->collaborateurEducationNatureEtudes;
    }

    public function setCollaborateurEducationNatureEtudes(?string $collaborateurEducationNatureEtudes): static
    {
        $this->collaborateurEducationNatureEtudes = $collaborateurEducationNatureEtudes;
        return $this;
    }

    public function getCollaborateurEducationEtablissement(): ?string
    {
        return $this->collaborateurEducationEtablissement;
    }

    public function setCollaborateurEducationEtablissement(?string $collaborateurEducationEtablissement): static
    {
        $this->collaborateurEducationEtablissement = $collaborateurEducationEtablissement;
        return $this;
    }

    public function getCollaborateurEducationAnneeObtention(): ?int
    {
        return $this->collaborateurEducationAnneeObtention;
    }
    
    public function setCollaborateurEducationAnneeObtention(?int $collaborateurEducationAnneeObtention): static
    {
        $this->collaborateurEducationAnneeObtention = $collaborateurEducationAnneeObtention;
        return $this;
    }

    public function getTypeDiplome(): ?TypeDiplome
    {
        return $this->typeDiplome;
    }

    public function setTypeDiplome(?TypeDiplome $typeDiplome): static
    {
        $this->typeDiplome = $typeDiplome;
        return $this;
    }

    public function getCollaborateurs(): Collection
    {
        return $this->collaborateurs;
    }

    public function addCollaborateur(Collaborateur $collaborateur): static
    {
        if (!$this->collaborateurs->contains($collaborateur)) {
            $this->collaborateurs->add($collaborateur);
            $collaborateur->addEducation($this);
        }
        return $this;
    }

    public function removeCollaborateur(Collaborateur $collaborateur): static
    {
        if ($this->collaborateurs->removeElement($collaborateur)) {
            $collaborateur->removeEducation($this);
        }
        return $this;
    }
}