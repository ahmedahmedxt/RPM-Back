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
    #[ORM\Column(name: "referenceDocumentsId", type: "integer")]
    private ?int $referenceDocumentsId = null;

    #[ORM\Column(name: "referenceDocumentsLibelle", length: 255, nullable: true)]
    private ?string $referenceDocumentsLibelle = null;

    #[ORM\Column(name: "referenceDocumentPath", length: 255, nullable: true)]
    private ?string $referenceDocumentPath = null;

    #[ORM\Column(name: "referenceDocumentsDate", type: "date", nullable: true)]
    private ?\DateTimeInterface $referenceDocumentsDate = null;

    #[ORM\Column(name: "referenceDocumentsCommentaire", type: "string", length: 500, nullable: true)]
    private ?string $referenceDocumentsCommentaire = null;

    #[ORM\ManyToOne(inversedBy: 'referenceDocuments')]
    #[ORM\JoinColumn(name: "typeDocumentId", referencedColumnName: "typeDocumentId", nullable: false)]
    private ?TypeDocument $typeDocument = null;

    #[ORM\ManyToOne(inversedBy: 'referenceDocuments')]
    #[ORM\JoinColumn(name: "referenceID", referencedColumnName: "referenceID", nullable: false, onDelete: "CASCADE")]
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

    public function getReferenceDocumentPath(): ?string
    {
        return $this->referenceDocumentPath;
    }

    public function setReferenceDocumentPath(?string $referenceDocumentPath): static
    {
        $this->referenceDocumentPath = $referenceDocumentPath;
        return $this;
    }

    public function getReferenceDocumentsDate(): ?\DateTimeInterface
    {
        return $this->referenceDocumentsDate;
    }

    public function setReferenceDocumentsDate(?\DateTimeInterface $referenceDocumentsDate): static
    {
        $this->referenceDocumentsDate = $referenceDocumentsDate;
        return $this;
    }

    public function getReferenceDocumentsCommentaire(): ?string
    {
        return $this->referenceDocumentsCommentaire;
    }

    public function setReferenceDocumentsCommentaire(?string $referenceDocumentsCommentaire): static
    {
        $this->referenceDocumentsCommentaire = $referenceDocumentsCommentaire;
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
