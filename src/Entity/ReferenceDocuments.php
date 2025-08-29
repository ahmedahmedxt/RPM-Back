<?php

namespace App\Entity;

use App\Repository\ReferenceDocumentsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReferenceDocumentsRepository::class)]
#[ORM\Table(name: 'referencedocuments')]
class ReferenceDocuments
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: "referenceDocumentsId")]
    private ?int $referenceDocumentsId = null;

    #[ORM\Column(name: "referenceDocumentsLibelle", length: 255, nullable: true)]
    private ?string $referenceDocumentsLibelle = null;

    #[ORM\ManyToOne(inversedBy: 'referenceDocuments')]
    #[ORM\JoinColumn(name: "typeDocumentId", referencedColumnName: "typeDocumentId")]
    private ?TypeDocument $typeDocument = null;

    #[ORM\ManyToOne(inversedBy: 'referenceDocuments')]
    #[ORM\JoinColumn(name: "referenceID", referencedColumnName: "referenceID")]
    private ?Reference $reference = null;

    public function getReferenceDocumentsId(): ?int
    {
        return $this->referenceDocumentsId;
    }

    public function getReferenceDocumentsLibelle(): ?string
    {
        return $this->referenceDocumentsLibelle;
    }

    public function setReferenceDocumentsLibelle(?string $referenceDocumentsLibelle): static
    {
        $this->referenceDocumentsLibelle = $referenceDocumentsLibelle;

        return $this;
    }

    public function getTypeDocument(): ?TypeDocument
    {
        return $this->typeDocument;
    }

    public function setTypeDocument(?TypeDocument $typeDocument): static
    {
        $this->typeDocument = $typeDocument;

        return $this;
    }

    public function getReference(): ?Reference
    {
        return $this->reference;
    }

    public function setReference(?Reference $reference): static
    {
        $this->reference = $reference;

        return $this;
    }
}
