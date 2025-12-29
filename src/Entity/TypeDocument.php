<?php

namespace App\Entity;

use App\Repository\TypeDocumentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TypeDocumentRepository::class)]
#[ORM\Table(name: 'typedocument')]
class TypeDocument
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: "typeDocumentId")]
    private ?int $typeDocumentId = null;

    #[ORM\Column(name : "typeDocumentLibelle", length: 255, nullable: true)]
    private ?string $typeDocumentLibelle = null;

    #[ORM\OneToMany(targetEntity: ReferenceDocuments::class, mappedBy: 'typeDocument')]
    private Collection $referenceDocuments;



    #[ORM\OneToMany(targetEntity: CollaborateurDocuments::class, mappedBy: 'collaborateurDocumentsType')]
    private Collection $collaborateurDocuments;
    

    public function __construct()
    {
        $this->referenceDocuments = new ArrayCollection();
        $this->collaborateurDocuments = new ArrayCollection();
    }

    public function getTypeDocumentId(): ?int
    {
        return $this->typeDocumentId;
    }

    public function getTypeDocumentLibelle(): ?string
    {
        return $this->typeDocumentLibelle;
    }

    public function setTypeDocumentLibelle(?string $typeDocumentLibelle): static
    {
        $this->typeDocumentLibelle = $typeDocumentLibelle;

        return $this;
    }

    public function getCollaborateurDocuments(): Collection
    {
        return $this->collaborateurDocuments;
    }
    
    public function addCollaborateurDocument(CollaborateurDocuments $collaborateurDocument): static
    {
        if (!$this->collaborateurDocuments->contains($collaborateurDocument)) {
            $this->collaborateurDocuments->add($collaborateurDocument);
            $collaborateurDocument->setCollaborateurDocumentsType($this);
        }
        return $this;
    }
    
    public function removeCollaborateurDocument(CollaborateurDocuments $collaborateurDocument): static
    {
        if ($this->collaborateurDocuments->removeElement($collaborateurDocument)) {
            if ($collaborateurDocument->getCollaborateurDocumentsType() === $this) {
                $collaborateurDocument->setCollaborateurDocumentsType(null);
            }
        }
        return $this;
    }


    /**
     * @return Collection<int, ReferenceDocuments>
     */
    public function getReferenceDocuments(): Collection
    {
        return $this->referenceDocuments;
    }

    public function addReferenceDocument(ReferenceDocuments $referenceDocument): static
    {
        if (!$this->referenceDocuments->contains($referenceDocument)) {
            $this->referenceDocuments->add($referenceDocument);
            $referenceDocument->setTypeDocument($this);
        }

        return $this;
    }

    public function removeReferenceDocument(ReferenceDocuments $referenceDocument): static
    {
        if ($this->referenceDocuments->removeElement($referenceDocument)) {
            // set the owning side to null (unless already changed)
            if ($referenceDocument->getTypeDocument() === $this) {
                $referenceDocument->setTypeDocument(null);
            }
        }

        return $this;
    }

}
