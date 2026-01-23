<?php

namespace App\Entity;

use App\Repository\ReferenceDocumentsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReferenceDocumentsRepository::class)]
#[ORM\Table(
    name: 'referencedocuments',
    uniqueConstraints: [
        new ORM\UniqueConstraint(
            name: 'uniq_ref_typedoc',
            columns: ['referenceID', 'typeDocumentId']
        )
    ]
)]
class ReferenceDocuments
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: "referenceDocumentsId", type: "integer")]
    private ?int $referenceDocumentsId = null;

    #[ORM\Column(name: "referenceDocumentsLibelle", length: 255, nullable: true)]
    private ?string $referenceDocumentsLibelle = null;

    #[ORM\Column(name: "referenceDocumentsObjet", length: 255, nullable: true)]
    private ?string $referenceDocumentsObjet = null;

    #[ORM\Column(name: "referenceDocumentPath", length: 255, nullable: true)]
    private ?string $referenceDocumentPath = null;

    #[ORM\Column(name: "referenceDocumentsDate", type: "date", nullable: true)]
    private ?\DateTimeInterface $referenceDocumentsDate = null;

    #[ORM\Column(name: "referenceDocumentsCommentaire", type: "string", length: 255, nullable: true)]
    private ?string $referenceDocumentsCommentaire = null;

    #[ORM\ManyToOne(inversedBy: 'referenceDocuments')]
    #[ORM\JoinColumn(name: "typeDocumentId", referencedColumnName: "typeDocumentId", nullable: false)]
    private ?TypeDocument $typeDocument = null;

    #[ORM\ManyToOne(inversedBy: 'referenceDocuments')]
    #[ORM\JoinColumn(name: "referenceID", referencedColumnName: "referenceID", nullable: false, onDelete: "CASCADE")]
    private ?Reference $reference = null;

    public function getReferenceDocumentsId(): ?int { return $this->referenceDocumentsId; }

    public function getReferenceDocumentsLibelle(): ?string { return $this->referenceDocumentsLibelle; }
    public function setReferenceDocumentsLibelle(?string $v): static { $this->referenceDocumentsLibelle = $v; return $this; }

    public function getReferenceDocumentsObjet(): ?string { return $this->referenceDocumentsObjet; }
    public function setReferenceDocumentsObjet(?string $v): static { $this->referenceDocumentsObjet = $v; return $this; }

    public function getReferenceDocumentPath(): ?string { return $this->referenceDocumentPath; }
    public function setReferenceDocumentPath(?string $v): static { $this->referenceDocumentPath = $v; return $this; }

    public function getReferenceDocumentsDate(): ?\DateTimeInterface { return $this->referenceDocumentsDate; }
    public function setReferenceDocumentsDate(?\DateTimeInterface $v): static { $this->referenceDocumentsDate = $v; return $this; }

    public function getReferenceDocumentsCommentaire(): ?string { return $this->referenceDocumentsCommentaire; }
    public function setReferenceDocumentsCommentaire(?string $v): static { $this->referenceDocumentsCommentaire = $v; return $this; }

    public function getTypeDocument(): ?TypeDocument { return $this->typeDocument; }
    public function setTypeDocument(?TypeDocument $v): static { $this->typeDocument = $v; return $this; }

    public function getReference(): ?Reference { return $this->reference; }
    public function setReference(?Reference $v): static { $this->reference = $v; return $this; }
}
