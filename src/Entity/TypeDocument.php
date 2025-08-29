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

    #[ORM\OneToMany(targetEntity: EmployeDocuments::class, mappedBy: 'employeDocumentsType')]
    private Collection $employeDocuments;

    public function __construct()
    {
        $this->referenceDocuments = new ArrayCollection();
        $this->employeDocuments = new ArrayCollection();
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
            $employeDocument->setEmployeDocumentsType($this);
        }

        return $this;
    }

    public function removeEmployeDocument(EmployeDocuments $employeDocument): static
    {
        if ($this->employeDocuments->removeElement($employeDocument)) {
            // set the owning side to null (unless already changed)
            if ($employeDocument->getEmployeDocumentsType() === $this) {
                $employeDocument->setEmployeDocumentsType(null);
            }
        }

        return $this;
    }
}
